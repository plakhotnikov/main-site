document.addEventListener('DOMContentLoaded', () => {
  const cityButtons = document.querySelectorAll('.ticket-city-button');
  const cityInput = document.getElementById('city');
  const roundtripToggle = document.getElementById('roundtrip');
  const returnField = document.getElementById('return-field');
  const returnInput = document.getElementById('date_to');

  if (cityButtons.length && cityInput) {
    cityButtons.forEach((button) => {
      button.addEventListener('click', () => {
        cityInput.value = button.dataset.city;
        cityButtons.forEach((item) => item.classList.remove('is-active'));
        button.classList.add('is-active');
      });
    });
  }

  const updateReturnField = () => {
    if (!roundtripToggle || !returnField || !returnInput) return;
    const isChecked = roundtripToggle.checked;
    returnField.style.display = isChecked ? 'flex' : 'none';
    returnInput.disabled = !isChecked;
  };

  if (roundtripToggle) {
    roundtripToggle.addEventListener('change', updateReturnField);
    updateReturnField();
  }
});
