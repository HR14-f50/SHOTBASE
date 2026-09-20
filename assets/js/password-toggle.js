document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
  const input = document.getElementById(button.dataset.passwordToggle);
  if (!input) return;
  button.addEventListener("click", function () {
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    button.textContent = show ? "隠す" : "表示";
    button.setAttribute("aria-pressed", String(show));
    button.setAttribute(
      "aria-label",
      show ? "パスワードを隠す" : "パスワードを表示",
    );
  });
});
