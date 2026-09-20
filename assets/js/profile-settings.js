document.querySelectorAll("[data-icon-builder]").forEach((builder) => {
  const refresh = () => {
    const motif = builder.querySelector('[name="icon_motif"]:checked').value;
    const color = builder.querySelector('[name="icon_color"]:checked').value;
    builder.querySelector("[data-icon-preview]").src =
      `${builder.dataset.base}/avatar.php?motif=${encodeURIComponent(motif)}&color=${encodeURIComponent(color)}`;
  };
  builder.addEventListener("change", refresh);
});
const settingsForm = document.querySelector("[data-settings-form]");
if (settingsForm) {
  const refresh = () => {
    const source = settingsForm.querySelector(
      '[name="icon_source"]:checked',
    )?.value;
    settingsForm
      .querySelectorAll("[data-icon-source-panel]")
      .forEach((panel) => {
        panel.hidden = panel.dataset.iconSourcePanel !== source;
        panel.querySelectorAll("input, select").forEach((input) => {
          input.disabled = panel.hidden;
        });
      });
    const mode = settingsForm.querySelector('[name="watermark_source"]')?.value;
    const custom = settingsForm.querySelector('[name="watermark_text"]');
    if (custom) custom.required = mode === "custom";
  };
  settingsForm.addEventListener("change", refresh);
  refresh();
}
const avatarInput = document.querySelector("[data-avatar-upload]");
let avatarObjectUrl;
if (avatarInput)
  avatarInput.addEventListener("change", () => {
    const file = avatarInput.files[0];
    avatarInput.setCustomValidity(
      file && file.size >= 20 * 1024 * 1024
        ? "20MB未満の画像を選択してください。"
        : "",
    );
    if (avatarObjectUrl) URL.revokeObjectURL(avatarObjectUrl);
    if (file && avatarInput.checkValidity()) {
      avatarObjectUrl = URL.createObjectURL(file);
      document.querySelector("[data-upload-preview]").src = avatarObjectUrl;
    }
  });

// CSS object-position とサーバー側の正方形切り抜きで同じ割合を使用します。
if (avatarInput) {
  const preview = document.querySelector('[data-upload-preview]');
  const x = document.querySelector('[data-avatar-x]');
  const y = document.querySelector('[data-avatar-y]');
  const status = document.querySelector('[data-avatar-status]');
  const updateCrop = () => {
    if (!x || !y) return;
    preview.style.objectPosition = `${x.value}% ${y.value}%`;
  };
  x?.addEventListener('input', updateCrop);
  y?.addEventListener('input', updateCrop);
  preview.addEventListener('load', () => {
    updateCrop();
    if (status && avatarInput.files.length) status.textContent = 'スライダーで切り抜く位置を調整してください。正方形の画像は位置を変えても表示が変わりません。';
  });
  preview.addEventListener('error', () => {
    avatarInput.setCustomValidity('画像を読み込めません。JPEG・PNG・WebPを選択してください。');
    if (status) status.textContent = '画像を読み込めませんでした。別の画像を選択してください。';
  });
  updateCrop();
}
