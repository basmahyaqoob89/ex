const BASE = '/AMARA/api/';


async function postJSON(endpoint, data = {}) {
    const url = BASE + endpoint;
    const form = new FormData();
    for (const k in data) form.append(k, data[k]);

    try {
        const res = await fetch(url, {
            method: 'POST',
            body: form,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const text = await res.text();

        let json;
        try {
            json = text ? JSON.parse(text) : null;
        } catch (err) {
            // ✅ هذا السطر هو المفتاح
            alert("Raw server response:\n\n" + text);
            console.error("Invalid JSON from server:", url, text);
            return { success: false, message: "Server returned invalid response." };
        }

        if (!json) {
            alert("Empty server response");
            return { success: false, message: "Empty server response." };
        }

        return json;

    } catch (err) {
        alert("Server connection failed: " + err.message);
        return { success: false, message: "Connection failed." };
    }
}

// ⭐ حذف الموعد
async function cancelApp(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        const resp = await postJSON('cancel_appointment.php', { id: appointmentId });
        if (resp.success) {
            alert('Appointment cancelled successfully.');
            window.location.reload(); 
        } else {
            alert(resp.message || 'Could not cancel appointment.');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. تسجيل دخول المستخدم ---
    const loginForm = document.querySelector('#loginForm');
    if (loginForm && !window.location.pathname.includes('adminLogIn.html')) {
        loginForm.addEventListener('submit', async function(e){
            e.preventDefault();
            const data = {
                email: this.querySelector('#email').value.trim(),
                password: this.querySelector('#password').value
            };
            const resp = await postJSON('logIn.php', data); 
            if (resp.success) {
                window.location.href = resp.redirect;
            } else {
                alert(resp.message || (resp.errors && resp.errors.join('\n')) || 'Login failed');
            }
        });
    }

    // --- 2. ✅ تسجيل حساب جديد (رسالتين مختلفتين) ---
    const signupForm = document.querySelector('#signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                password: document.getElementById('password').value,
                'confirm-password': document.getElementById('confirm-password').value
            };

            const resp = await postJSON('signup.php', data);

            if (resp && resp.success === true) {
                alert("Welcome to AMARA! Your account has been created successfully. Please log in to continue.");
                window.location.href = 'logIn.html';
                return;
            }

            if (resp && resp.code === 'account_exists') {
                alert("Your account already exists. Please log in to continue.");
                window.location.href = 'logIn.html';
                return;
            }

            alert(
                (resp && (resp.message || (resp.errors && resp.errors.join('\n'))))
                || 'Registration failed'
            );
        });
    }

    // --- 3. إرسال التقييم ---
    const reviewForm = document.querySelector('#reviewForm'); 
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const commentVal = document.getElementById('reviewText').value; 
            const ratingVal = document.querySelector('input[name="rating"]:checked')?.value || 5;

            const resp = await postJSON('reviewPage.php', {
                comment: commentVal,
                rating: ratingVal
            });

            if (resp.success) {
                alert('Thank you! Your review has been successfully submitted and will be reviewed by management.');
                reviewForm.reset();
            } else {
                alert(resp.message || 'Failed to submit review.');
            }
        });
    }

    // --- 4. عرض التقييمات ---
    const reviewsContainer = document.querySelector('.reviews-container');
    if (reviewsContainer && window.location.pathname.includes('reviewPage.html')) {
        fetch(BASE + 'reviewPage.php')
            .then(res => res.json())
            .then(data => {
                reviewsContainer.innerHTML = '';
                if (data.success && data.reviews.length > 0) {
                    data.reviews.forEach(rev => {
                        const stars = '★'.repeat(rev.rating);
                        reviewsContainer.innerHTML += `
                            <div class="review-card">
                                <div class="review-content">
                                    <strong>${stars}</strong>
                                    <p>${rev.comment}</p>
                                    <small>${rev.userName} - ${rev.created_at || rev.review_date}</small>
                                </div>
                            </div>`;
                    });
                } else {
                    reviewsContainer.innerHTML = '<p style="text-align:center;">No published reviews yet.</p>';
                }
            })
            .catch(() => {
                reviewsContainer.innerHTML = '<p style="text-align:center; color:red;">Unable to load reviews at the moment.</p>';
            });
    }
});
