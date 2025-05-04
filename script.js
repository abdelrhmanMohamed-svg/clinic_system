// Open and close the pop-up form
const addPatientBtn = document.getElementById('addPatientBtn');
const popupForm = document.getElementById('popupForm');
const closeBtn = document.querySelector('.close');
const cancelBtn = document.querySelector('.cancelBtn');

addPatientBtn.addEventListener('click', () => {
    popupForm.style.display = 'flex';
});

closeBtn.addEventListener('click', () => {
    popupForm.style.display = 'none';
});

cancelBtn.addEventListener('click', () => {
    popupForm.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === popupForm) {
        popupForm.style.display = 'none';
    }
});
