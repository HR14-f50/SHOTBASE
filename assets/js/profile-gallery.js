/** ピックアップは1行の横スクロール。タッチ・キーボード・左右ボタンに対応。 */
document.querySelectorAll("[data-pickup-gallery]").forEach((gallery) => {
  const section = gallery.closest(".pickupSection");
  const buttons = section.querySelectorAll("[data-gallery-direction]");
  const refresh = () => {
    buttons.forEach((button) => {
      button.disabled =
        Number(button.dataset.galleryDirection) < 0
          ? gallery.scrollLeft <= 1
          : gallery.scrollLeft >= gallery.scrollWidth - gallery.clientWidth - 1;
    });
  };
  buttons.forEach((button) =>
    button.addEventListener("click", () => {
      gallery.scrollBy({
        left:
          Number(button.dataset.galleryDirection) * gallery.clientWidth * 0.8,
        behavior: matchMedia("(prefers-reduced-motion: reduce)").matches
          ? "auto"
          : "smooth",
      });
    }),
  );
  gallery.addEventListener("scroll", refresh, { passive: true });
  gallery
    .querySelectorAll("img")
    .forEach((img) => img.addEventListener("load", refresh));
  if ("ResizeObserver" in window) new ResizeObserver(refresh).observe(gallery);
  refresh();
});

const archiveDisclosure = document.querySelector(".archiveDisclosure");
if (archiveDisclosure) {
  const mobile = matchMedia("(max-width: 700px)");
  archiveDisclosure.open = !mobile.matches;
  mobile.addEventListener("change", () => {
    archiveDisclosure.open = !mobile.matches;
  });
}
