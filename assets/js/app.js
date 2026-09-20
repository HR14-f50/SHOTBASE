document.addEventListener('DOMContentLoaded', function () {
  var closeButtons = document.querySelectorAll('.js-modal-close');

  closeButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var modal = button.closest('.modalOverlay');

      if (modal) {
        modal.classList.remove('is-open');
      }
    });
  });

  document.querySelectorAll('form[data-confirm], .dangerForm').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var message = form.dataset.confirm || '削除しますか？';

      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });

  document.addEventListener('contextmenu', function (event) {
    var image = event.target.closest('img');

    if (image) {
      event.preventDefault();
    }
  });

  document.addEventListener('dragstart', function (event) {
    var image = event.target.closest('img');

    if (image) {
      event.preventDefault();
    }
  });
});
