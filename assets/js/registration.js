const signupIcon = document.getElementById("signupIcon");
if (signupIcon) {
  const preview = document.getElementById("signupIconPreview");
  const original = preview.src;
  let objectUrl;
  signupIcon.addEventListener("change", function () {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    signupIcon.setCustomValidity("");
    const file = signupIcon.files[0];
    if (!file) {
      preview.src = original;
      return;
    }
    if (file.size >= 20 * 1024 * 1024) {
      signupIcon.setCustomValidity("20MB未満の画像を選択してください。");
      signupIcon.reportValidity();
      preview.src = original;
      return;
    }
    objectUrl = URL.createObjectURL(file);
    preview.src = objectUrl;
  });
  window.addEventListener("pagehide", function () {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
  });
}
