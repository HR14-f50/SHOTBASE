/**
  * data-counter-target に指定されたIDの要素へ、入力文字数を表示します。
  * maxlength と同じく JavaScript の文字列の長さ（UTF-16単位）で数えます。
  */
function initCharacterCounters(root = document) {
  root.querySelectorAll('[data-counter-target]').forEach(function (input) {
    const counter = document.getElementById(input.dataset.counterTarget);

    if (!counter || input.dataset.counterInitialized === 'true') {
      return;
    }

    function updateCounter() {
      counter.textContent = input.value.length;
    }

    input.addEventListener('input', updateCounter);
    input.dataset.counterInitialized = 'true';
    updateCounter();
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    initCharacterCounters();
  });
} else {
  initCharacterCounters();
}
