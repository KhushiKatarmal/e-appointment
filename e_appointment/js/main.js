document.addEventListener('DOMContentLoaded', function () {
    const appointmentDateInput = document.querySelector('#appointment-date');
    if (appointmentDateInput) {
        const today = new Date().toISOString().split('T')[0];
        appointmentDateInput.setAttribute('min', today);
    }

    const cancelForms = document.querySelectorAll('.cancel-form');
    cancelForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const confirmed = window.confirm('Are you sure you want to cancel this appointment?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    const doctorSearch = document.getElementById('doctorSearch');
    const noDoctorsMessage = document.getElementById('doctorSearchEmpty');
    if (doctorSearch) {
        function filterDoctorCards() {
            const query = doctorSearch.value.trim().toLowerCase();
            const terms = query.split(/\s+/).filter(Boolean);
            const cards = document.querySelectorAll('#doctorGrid .doctor-card');
            let visibleCount = 0;

            cards.forEach(function (card) {
                const name = card.dataset.name || '';
                const specialty = card.dataset.specialty || '';
                const keywords = card.dataset.keywords || '';
                const combined = [name, specialty, keywords].join(' ');
                const matches = !query || terms.every(function (term) {
                    return combined.includes(term);
                });
                card.style.display = matches ? 'grid' : 'none';
                if (matches) {
                    visibleCount++;
                }
            });

            if (noDoctorsMessage) {
                noDoctorsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        doctorSearch.addEventListener('input', filterDoctorCards);
        doctorSearch.addEventListener('search', filterDoctorCards);
        filterDoctorCards();
    }

    const availableDates = window.__availableDates || {};
    const dateRow = document.querySelector('.date-row');
    const slotBox = document.querySelector('.slot-options');
    const dateInput = document.getElementById('dateInput');
    const timeInput = document.getElementById('timeInput');
    const bookingForm = document.getElementById('bookingForm');

    function selectDate(date) {
        if (!dateRow || !slotBox || !dateInput) return;
        const normalizedDate = availableDates[date] ? date : '';
        dateInput.value = normalizedDate;
        const pills = dateRow.querySelectorAll('.date-pill');
        pills.forEach(function (pill) {
            pill.classList.toggle('active', pill.dataset.date === normalizedDate);
        });

        if (!normalizedDate) {
            timeInput.value = '';
            slotBox.innerHTML = '<div class="alert">No slots available for the selected day.</div>';
            return;
        }

        renderTimeSlots(normalizedDate);
    }

    function renderTimeSlots(date) {
        if (!slotBox || !timeInput) return;
        const slots = availableDates[date] || [];
        slotBox.innerHTML = '';
        if (!slots.length) {
            const notice = document.createElement('div');
            notice.className = 'alert';
            notice.textContent = 'No slots available for the selected day.';
            slotBox.appendChild(notice);
            timeInput.value = '';
            return;
        }

        const currentTime = timeInput.value;
        const selectedTime = currentTime && slots.includes(currentTime) ? currentTime : slots[0];
        timeInput.value = selectedTime;

        slots.forEach(function (slot) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'slot-pill' + (selectedTime === slot ? ' active' : '');
            button.dataset.time = slot;
            button.textContent = slot;
            slotBox.appendChild(button);
        });
    }

    if (dateRow) {
        dateRow.addEventListener('click', function (event) {
            const button = event.target.closest('.date-pill');
            if (!button) return;
            selectDate(button.dataset.date);
        });
    }

    if (slotBox) {
        slotBox.addEventListener('click', function (event) {
            const button = event.target.closest('.slot-pill');
            if (!button) return;
            const selectedTime = button.dataset.time;
            timeInput.value = selectedTime;
            slotBox.querySelectorAll('.slot-pill').forEach(function (pill) {
                pill.classList.toggle('active', pill.dataset.time === selectedTime);
            });
        });
    }

    const scheduleTable = document.querySelector('.schedule-table.schedule-clickable');
    if (scheduleTable) {
        scheduleTable.addEventListener('click', function (event) {
            const row = event.target.closest('.schedule-row');
            if (!row) return;
            const date = row.dataset.date;
            if (!date) return;
            selectDate(date);
        });
    }

    if (bookingForm) {
        bookingForm.addEventListener('submit', function (event) {
            const doctorInput = document.getElementById('doctorIdInput');
            if (!dateInput || !timeInput || !doctorInput) return;
            if (!doctorInput.value || !dateInput.value || !timeInput.value) {
                event.preventDefault();
                alert('Please select a doctor, date, and time before booking.');
            }
        });
    }

    const initialDate = dateInput && dateInput.value ? dateInput.value : Object.keys(availableDates)[0] || '';
    if (initialDate) {
        selectDate(initialDate);
    }

    // Password visibility toggles
    const pwToggles = document.querySelectorAll('.toggle-password');
    pwToggles.forEach(function (btn) {
        const targetId = btn.dataset.target;
        const input = document.getElementById(targetId);
        if (!input) return;
        btn.addEventListener('click', function () {
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.94 17.94A10.06 10.06 0 0 1 12 20c-7 0-11-8-11-8a20.6 20.6 0 0 1 5.06-4.94" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 1l22 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            }
        });
    });
    // Event delegation fallback in case buttons are added later or listeners not attached
    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest && ev.target.closest('.toggle-password');
        if (!btn) return;
        const targetId = btn.dataset.target;
        const input = document.getElementById(targetId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.94 17.94A10.06 10.06 0 0 1 12 20c-7 0-11-8-11-8a20.6 20.6 0 0 1 5.06-4.94" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M1 1l22 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
    });
});

function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const overlayId = form.closest('body').querySelector('#registerLoadingOverlay') ? 'registerLoadingOverlay' : 'loginLoadingOverlay';
    const overlay = document.getElementById(overlayId);
    if (!overlay || !form) {
        return true;
    }

    overlay.classList.add('active');
    setTimeout(function () {
        form.submit();
    }, 1200);
    return false;
}
