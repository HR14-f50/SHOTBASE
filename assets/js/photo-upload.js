(() => {
  const form = document.getElementById('photoUploadForm');
  if (!form) return;
  const picker = document.getElementById('photoFiles');
  const dropArea = document.getElementById('uploadDropArea');
  const queue = document.getElementById('photoQueue');
  const errors = document.getElementById('uploadErrors');
  const clear = document.getElementById('clearPhotos');
  const submit = document.getElementById('submitPhotos');
  const progress = document.getElementById('uploadProgress');
  const status = document.getElementById('uploadStatus');
  const maxFiles = Number(form.dataset.maxFiles);
  const maxBytes = Number(form.dataset.maxBytes);
  let items = [];
  let batchId = null;
  let busy = false;
  let completed = false;

  function showErrors(messages) {
    errors.replaceChildren();
    messages.forEach((message) => {
      const p = document.createElement('p');
      p.textContent = message;
      errors.appendChild(p);
    });
    errors.hidden = messages.length === 0;
  }

  function render() {
    queue.replaceChildren();
    items.forEach((item, index) => {
      const li = document.createElement('li');
      li.className = 'queuedPhoto';
      const image = document.createElement('img');
      image.src = item.url;
      image.alt = item.file.name;
      image.loading = 'lazy';
      const details = document.createElement('div');
      const name = document.createElement('p');
      name.className = 'queuedPhotoName';
      name.textContent = item.file.name;
      const state = document.createElement('p');
      state.className = 'queuedPhotoState';
      state.textContent = item.message || (item.file.size / 1024 / 1024).toFixed(2) + ' MB';
      if (item.state === 'error') state.classList.add('isError');
      if (item.state === 'done') state.classList.add('isDone');
      details.append(name, state);
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'removeQueuedPhoto';
      remove.textContent = '×';
      remove.setAttribute('aria-label', item.file.name + 'を選択から削除');
      remove.disabled = busy || batchId !== null;
      remove.addEventListener('click', () => {
        URL.revokeObjectURL(item.url);
        items.splice(index, 1);
        render();
      });
      li.append(image, details, remove);
      queue.appendChild(li);
    });
    document.getElementById('uploadCount').textContent = items.length + ' / ' + maxFiles;
    document.getElementById('emptyPhotoQueue').hidden = items.length > 0;
    picker.disabled = busy || batchId !== null || maxFiles === 0;
    clear.disabled = busy || items.length === 0 || batchId !== null;
    document.getElementById('uploadTags').disabled = busy || batchId !== null;
    submit.disabled = busy || items.length === 0 || completed;
    submit.textContent = completed ? 'アップロード完了' : batchId ? '未完了の写真を再試行' : '写真をアップロード';
    form.setAttribute('aria-busy', String(busy));
  }

  function addFiles(files) {
    if (busy || batchId !== null) return;
    const messages = [];
    Array.from(files).forEach((file) => {
      if (file.size >= maxBytes) {
        messages.push(file.name + '：20MB以上のため追加できません。');
      } else if (!file.size) {
        messages.push(file.name + '：空のファイルは追加できません。');
      } else if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) && !(file.type === '' && /\.(jpe?g|png|webp)$/i.test(file.name))) {
        messages.push(file.name + '：JPEG・PNG・WebPを選んでください。');
      } else if (items.some((item) => item.file.name === file.name && item.file.size === file.size && item.file.lastModified === file.lastModified)) {
        messages.push(file.name + '：すでに選択されています。');
      } else if (items.length >= maxFiles) {
        messages.push('今回追加できる写真は' + maxFiles + '枚までです。残りは次回追加してください。');
      } else {
        items.push({file, url: URL.createObjectURL(file), state: 'ready', message: ''});
      }
    });
    showErrors([...new Set(messages)]);
    render();
  }

  picker.addEventListener('change', () => {
    addFiles(picker.files);
    picker.value = '';
  });
  clear.addEventListener('click', () => {
    items.forEach((item) => URL.revokeObjectURL(item.url));
    items = [];
    showErrors([]);
    render();
  });
  ['dragenter', 'dragover'].forEach((name) => dropArea.addEventListener(name, (event) => {
    event.preventDefault();
    if (!busy && !batchId) dropArea.classList.add('isDragging');
  }));
  ['dragleave', 'drop'].forEach((name) => dropArea.addEventListener(name, (event) => {
    event.preventDefault();
    dropArea.classList.remove('isDragging');
    if (name === 'drop') addFiles(event.dataTransfer.files);
  }));

  function send(data, onProgress) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', form.dataset.endpoint);
      xhr.timeout = 180000;
      xhr.upload.onprogress = (event) => {
        if (event.lengthComputable && onProgress) onProgress(event.loaded / event.total);
      };
      xhr.onload = () => {
        let body;
        try { body = JSON.parse(xhr.responseText); }
        catch { reject(new Error('サーバーの応答を確認できません。再試行してください。')); return; }
        if (xhr.status >= 200 && xhr.status < 300 && !body.error) resolve(body);
        else reject(new Error(body.error || '送信に失敗しました。'));
      };
      xhr.onerror = () => reject(new Error('通信が切れました。接続を確認して再試行してください。'));
      xhr.ontimeout = () => reject(new Error('処理に時間がかかっています。再試行してください。'));
      xhr.send(data);
    });
  }

  function requestData(action) {
    const data = new FormData();
    data.append('action', action);
    data.append('csrf', form.elements.csrf.value);
    data.append('project_id', form.elements.project_id.value);
    return data;
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (busy || !items.length || completed) return;
    if (items.length > maxFiles || items.some((item) => item.file.size >= maxBytes)) {
      showErrors(['写真は50枚以内・1枚20MB未満で選択してください。']);
      return;
    }
    busy = true;
    showErrors([]);
    render();
    document.getElementById('uploadProgressArea').hidden = false;
    try {
      if (!batchId) {
        const data = requestData('begin');
        data.append('file_count', String(items.length));
        data.append('new_tag_name', form.elements.new_tag_name.value);
        form.querySelectorAll('[name="tag_ids[]"]:checked').forEach((input) => data.append('tag_ids[]', input.value));
        batchId = (await send(data)).batch_id;
      }
      for (let index = 0; index < items.length; index++) {
        const item = items[index];
        if (item.state === 'done') continue;
        item.state = 'sending';
        item.message = '送信・圧縮中…';
        status.textContent = (index + 1) + ' / ' + items.length + '枚目を処理しています。';
        render();
        const data = requestData('upload');
        data.append('batch_id', batchId);
        data.append('item_index', String(index));
        data.append('photo', item.file);
        try {
          const result = await send(data, (fraction) => {
            progress.value = ((index + fraction * 0.9) / items.length) * 100;
          });
          item.state = 'done';
          item.message = '登録済み · ' + (result.bytes / 1024 / 1024).toFixed(2) + ' MB';
        } catch (error) {
          item.state = 'error';
          item.message = error.message;
        }
        progress.value = ((index + 1) / items.length) * 100;
      }
      const done = items.filter((item) => item.state === 'done').length;
      completed = done === items.length;
      status.textContent = done + ' / ' + items.length + '枚を登録しました。' + (completed ? '写真の管理画面で確認できます。' : '未完了の写真だけ再試行できます。');
    } catch (error) {
      showErrors([error.message]);
      status.textContent = '送信を開始できませんでした。';
    } finally {
      busy = false;
      render();
    }
  });
  window.addEventListener('beforeunload', (event) => {
    if (busy || (batchId && !completed)) {
      event.preventDefault();
      event.returnValue = '';
    }
  });
  window.addEventListener('pagehide', () => items.forEach((item) => URL.revokeObjectURL(item.url)));
  render();
})();
