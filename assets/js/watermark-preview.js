const preview = document.querySelector("[data-watermark-preview]");
if (preview) {
  const form = preview.closest("form");
  const image = preview.querySelector("img");
  const status = preview.querySelector('[role="status"]');
  let timer;
  let request;
  let objectUrl;
  async function updatePreview() {
    request?.abort();
    request = new AbortController();
    const data = new FormData();
    [
      "csrf",
      "nickname",
      "watermark_source",
      "watermark_text",
      "watermark_size",
      "watermark_color",
      "watermark_opacity",
    ].forEach((name) => {
      if (form.elements[name]) data.append(name, form.elements[name].value);
    });
    if (preview.dataset.photoId)
      data.append("photo_id", preview.dataset.photoId);
    data.append(
      "watermark_position",
      form.querySelector('[name="watermark_position"]:checked').value,
    );
    data.append(
      "watermark_enabled",
      form.elements.watermark_enabled
        ? form.elements.watermark_enabled.checked
          ? "1"
          : "0"
        : preview.dataset.enabled,
    );
    try {
      const response = await fetch(preview.dataset.watermarkPreview, {
        method: "POST",
        body: data,
        signal: request.signal,
      });
      if (!response.ok) throw new Error("プレビューを読み込めませんでした。");
      const blob = await response.blob();
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      objectUrl = URL.createObjectURL(blob);
      image.src = objectUrl;
      image.hidden = false;
      status.textContent = (
        form.elements.watermark_enabled
          ? form.elements.watermark_enabled.checked
          : preview.dataset.enabled === "1"
      )
        ? "保存前の設定を見本に反映しています。"
        : "ウォーターマークはオフです。";
    } catch (error) {
      if (error.name !== "AbortError") status.textContent = error.message;
    }
  }
  const inherit = form.querySelector("[data-default-position]");
  form.addEventListener("input", (event) => {
    if (inherit && event.target.name === "watermark_position")
      inherit.checked = false;
    if (inherit && event.target === inherit && inherit.checked) {
      form.querySelectorAll('[name="watermark_position"]').forEach((radio) => {
        radio.checked = radio.value === inherit.dataset.defaultPosition;
      });
    }
    if (
      event.target.name === "nickname" ||
      event.target === inherit ||
      event.target.name.startsWith("watermark_")
    ) {
      clearTimeout(timer);
      timer = setTimeout(updatePreview, 180);
    }
  });
  updatePreview();
  window.addEventListener("pagehide", () => {
    request?.abort();
    if (objectUrl) URL.revokeObjectURL(objectUrl);
  });
}
