console.log("Hello from backend module JS!");
document.addEventListener('DOMContentLoaded', function () {
 const container = document.getElementById("plans-container");
    const websiteId = container.dataset.websiteId;
    const packageId = container.dataset.packageId;
    const activeSubscrInterval = container.dataset.subscrInterval;
    const endDateStr = container.dataset.endDate;

    const toggle = document.getElementById("billing-toggle");
    const monthlyLabel = document.getElementById("monthly-label");
    const annualLabel = document.getElementById("annual-label");
    const monthlyclass = document.getElementById("monthlyclass");
    const annualclass = document.getElementById("annualclass");

    const isExpired = endDateStr && new Date(endDateStr) < new Date();

    // Update buttons text/status
    function updateButtons(container) {
        container.querySelectorAll('.upgrade-btn').forEach(btn => {
            const planAction = btn.dataset.action;
            if (isExpired) {
                btn.textContent = 'Upgrade';
                btn.dataset.action = 'upgrade';
                btn.classList.remove('cancel-btnn');
            } else {
                if (planAction === 'cancel') {
                    btn.textContent = 'Cancel';
                    btn.classList.add('cancel-btnn');
                } else {
                    btn.textContent = 'Upgrade';
                    btn.classList.remove('cancel-btnn');
                }
            }
        });
    }

    updateButtons(monthlyclass);
    updateButtons(annualclass);

    // Toggle monthly/annual display
    function showMonthly() {
        toggle.checked = false;
        monthlyLabel.classList.add("active");
        annualLabel.classList.remove("active");
        monthlyclass.style.display = "block";
        annualclass.style.display = "none";
    }

    function showAnnual() {
        toggle.checked = true;
        monthlyLabel.classList.remove("active");
        annualLabel.classList.add("active");
        monthlyclass.style.display = "none";
        annualclass.style.display = "block";
    }

    if (activeSubscrInterval === 'Y') showAnnual();
    else showMonthly();

    toggle.addEventListener("change", () => {
        toggle.checked ? showAnnual() : showMonthly();
    });

    // Attach event listeners to upgrade buttons
    document.querySelectorAll('.upgrade-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const planId = btn.dataset.planId;
            const actionType = btn.dataset.action;
            const interval = this.dataset.interval || 'Y';

            const payload = {
                website_id: websiteId,
                current_package_id: packageId,
                action: actionType
            };

            if (actionType === 'upgrade') {
                payload.package_id = planId;
                payload.interval = interval;
            }

            const formBody = new URLSearchParams(payload).toString();
            const newWindow = window.open('', '_blank');

            fetch('https://skynetaccessibilityscan.com/api/generate-plan-action-link', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formBody
            })
            .then(res => res.json())
            .then(data => {
                const redirectUrl = data.action_link || data.url;
               
                if (redirectUrl) newWindow.location.href = redirectUrl;
                else {
                    newWindow.close();
                    alert("No link returned from API");
                }
            })
            .catch(err => console.error("API Error:", err));
        });
    });
});
//cancel subscription button

document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.dataset.url;
            if (url) {
                window.open(url, '_blank');
            } else {
                alert("No URL available");
            }
        });
    });
// violation button autologin link

  document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.dataset.url;
            if (url && url !== '#') {
                window.open(url, '_blank');
            } else {
                alert("No violation link available");
            }
        });
    });

    
// Hide and show violation report section function
document.addEventListener('DOMContentLoaded', function () {
    const showBtn = document.getElementById("show-details-btn");
    const backBtn = document.getElementById("go-back-btn");

    const section1 = document.getElementById("section1");
    const section2 = document.getElementById("section2");

    showBtn?.addEventListener("click", () => {
        section1.style.display = "none";
        section2.style.display = "block";
    });

    backBtn?.addEventListener("click", () => {
        section2.style.display = "none";
        section1.style.display = "block";
    });
});
