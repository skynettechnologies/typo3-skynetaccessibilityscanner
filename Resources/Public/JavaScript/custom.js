document.addEventListener('DOMContentLoaded', function () {

  const configE2 = document.getElementById('skynet-config');

  window.skynetConfig = {
    website_id: configE2?.dataset.websiteId || '',
    user_id: configE2?.dataset.userId || '',
    paypal_subscr_id: configE2?.dataset.paypalId || '',
    package_id: configE2?.dataset.packageId || ''
  };

 

});


(function () {

  const $ = (id) => document.getElementById(id);

  /**
   * Toggle Email Form
   */
  function _skynetToggleEmailForm(e) {
    if (e) e.preventDefault();

    const wrapper = $('skynetEmailToggleWrapper');
    const panel   = $('skynetEmailFormPanel');
    if (!wrapper || !panel) return;

    const isOpen = wrapper.classList.contains('skynet-form-open');

    panel.style.display = isOpen ? 'none' : 'block';
    wrapper.classList.toggle('skynet-form-open', !isOpen);

    if (!isOpen) {
      const errEl = $('skynetEmailFormError');
      const okEl  = $('skynetEmailFormSuccess');
      if (errEl) errEl.style.display = 'none';
      if (okEl)  okEl.style.display  = 'none';
    }
  }

  /**
   * Save Email
   */
  async function _skynetSaveEmail(e) {
    if (e) e.preventDefault();

    const nameInput  = $('skynetRegName');
    const emailInput = $('skynetRegEmail');
    const errEl      = $('skynetEmailFormError');
    const okEl       = $('skynetEmailFormSuccess');
    const saveBtn    = $('skynetRegSaveBtn');
    const saveTxt    = $('skynetRegSaveBtnText');
    const spinner    = $('skynetRegSaveSpinner');

    [nameInput, emailInput].forEach(el => el && el.classList.remove('skynet-input-error'));
    if (errEl) errEl.style.display = 'none';
    if (okEl)  okEl.style.display  = 'none';

    const name  = nameInput?.value.trim() || '';
    const email = emailInput?.value.trim() || '';
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!name || !emailRe.test(email)) {
      if (!name) nameInput?.classList.add('skynet-input-error');
      if (!emailRe.test(email)) emailInput?.classList.add('skynet-input-error');

      if (errEl) {
        errEl.textContent = 'Please enter a valid name and email address.';
        errEl.style.display = 'block';
      }
      return;
    }

    saveBtn && (saveBtn.disabled = true);
    if (saveTxt) saveTxt.textContent = 'Saving…';
    if (spinner) spinner.style.display = 'inline-block';

    try {
      const websiteUrl = window.location.origin;
      let domain = '';

      try {
        domain = new URL(websiteUrl).hostname;
      } catch {
        domain = websiteUrl.replace(/^https?:\/\//, '').split('/')[0];
      }

      const userInfo = { name, email };

      await _skynetRegisterUser(websiteUrl, userInfo, domain);

      if (okEl) {
        okEl.textContent = 'Email registered successfully!';
        okEl.style.display = 'block';
      }

      setTimeout(() => {
        const wrapper = $('skynetEmailToggleWrapper');
        const parent  = wrapper?.closest('.userform');
        const divider = document.querySelector('.divider');

        if (parent) parent.style.display = 'none';
        if (divider) divider.style.display = 'none';

      }, 1200);

    } catch (err) {
      if (errEl) {
        errEl.textContent = err?.message || 'Registration failed. Please try again.';
        errEl.style.display = 'block';
      }
    } finally {
      saveBtn && (saveBtn.disabled = false);
      if (saveTxt) saveTxt.textContent = 'Save & Register';
      if (spinner) spinner.style.display = 'none';
    }
  }

  /**
   * Handle Email Toggle Visibility
   */
  function _skynetHandleEmailToggle(userInfoResponse) {
   
    const wrapper = $('skynetEmailToggleWrapper');
    if (!wrapper) return;

    const parent = wrapper.closest('.userform');
    const email = userInfoResponse?.email || "";

    const isFallback = !email || email.startsWith("no-reply@");

    if (parent) {
      parent.style.display = isFallback ? "block" : "none";
    }
  }

  /**
   * API Call
   */
function _skynetRegisterUser(websiteUrl, userInfo, domain) {
  
 const el = document.getElementById('user_id');

  if (!el) return;

  const userid = el.textContent.trim();

  console.log("User ID:", userid);
 
 const formdata = new FormData();
formdata.append("user_id", userid);
formdata.append("name", userInfo.name);
formdata.append("email", userInfo.email);

const requestOptions = {
  method: "POST",
  body: formdata,
  redirect: "follow"
};

fetch("https://skynetaccessibilityscan.com/api/update-user", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
}

  /**
   * ✅ IMPORTANT: Attach Events (CSP SAFE)
   */
  function initEvents() {
    const toggleBtn = $('skynetEmailToggleBtn');
    const saveBtn   = $('skynetRegSaveBtn');
    const cancelBtn = document.querySelector('.skynet-email-cancel-btn');

    if (toggleBtn) {
      toggleBtn.addEventListener('click', _skynetToggleEmailForm);
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', _skynetSaveEmail);
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', _skynetToggleEmailForm);
    }
  }

  /**
   * TYPO3 Safe Init
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEvents);
  } else {
    setTimeout(initEvents, 200);
  }

})();
document.addEventListener('click', function (e) {

    // Cancel button (open link)
    if (e.target && e.target.classList.contains('cancel-btn')) {
        const url = e.target.dataset.url;

        if (url) {
            window.open(url, '_blank');
        }
    }
    

});
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.view-btn');

    if (btn) {
       
        const url = btn.dataset.url;

        if (url && url !== '#') {
            window.open(url, '_blank');
        }
    }

});
  document.addEventListener('DOMContentLoaded', function () {
        const target = document.body;

        const observer = new MutationObserver(() => {
            document.querySelectorAll('input#billing-toggle + .checkbox')
                .forEach(el => el.remove());
        });

        observer.observe(target, { childList: true, subtree: true });
    });
document.addEventListener('DOMContentLoaded', function () {

    const configEl = document.getElementById('skynet-config');
   

    const config = {
        website_id: configEl?.dataset.websiteId || '',
       user_id: configEl?.dataset.userId || '',   // ✅ FIXED
        paypal_subscr_id: configEl?.dataset.paypalId || '',
        package_id: configEl?.dataset.packageId || ''
    };

    window.skynetConfig = config;

    document.addEventListener('click', function (e) {

        const btn = e.target.closest('.upgrade-btn');

        if (btn) {

            const planId = btn.dataset.plan;
            let actionType = btn.dataset.action;
            const interval = btn.dataset.interval;

            let websiteId = config.website_id;
            let paypalSubscrId = config.paypal_subscr_id;
            let currentPackageId = config.package_id;

            if (!paypalSubscrId || paypalSubscrId === 'null') {
                actionType = 'upgrade';
            }

            const payload = {
                website_id: websiteId,
                current_package_id: currentPackageId,
                action: actionType
            };

            if (actionType === 'upgrade') {
                payload.package_id = planId;
                payload.interval = interval;
            }

            console.log("Payload:", payload);

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

                if (redirectUrl) {
                    newWindow.location.href = redirectUrl;
                } else {
                    newWindow.close();
                    alert("No link returned from API");
                }
            })
            .catch(err => console.error("API Error:", err));
        }

    });

});
  document.addEventListener('DOMContentLoaded', function () {

    const configEl = document.getElementById('skynet-config');
  const configE2 = document.getElementById('skynet-config');
    const activeSubscrInterval = configEl?.dataset.subscrInterval || '';
    const endDateStr = configEl?.dataset.endDate || '';
    const paypalSubscrId = configEl?.dataset.paypalId || '';
    const cancelDateStr = configEl?.dataset.cancelDate || '';
    

    const todayStr = new Date().toISOString().split('T')[0];

    const toggle = document.getElementById("billing-toggle");
    const monthlyLabel = document.getElementById("monthly-label");
    const annualLabel = document.getElementById("annual-label");
    const monthlyclass = document.getElementById("monthlyclass");
    const annualclass = document.getElementById("annualclass");

  



const formatDate = (d) => d.split(' ')[0];

const cancelDateOnly = formatDate(cancelDateStr);
const endDateonly =formatDate(endDateStr);
const isCancelToday = cancelDateOnly && cancelDateOnly <= todayStr;
 const isenddateOnly = endDateonly <= todayStr;
const isExpired =
  endDateStr &&
  new Date(endDateStr.replace(' ', 'T')).getTime() < Date.now();


    function updateButtons(container) {
        container.querySelectorAll('.upgrade-btn').forEach(btn => {
            let planAction = btn.dataset.action;

            if (!paypalSubscrId || paypalSubscrId === 'null') {
                planAction = 'upgrade';
            }

            if (isExpired || isCancelToday) {
           
                btn.textContent = 'Upgrade';
                btn.dataset.action = 'upgrade';
                btn.classList.remove('cancel-btnn');
            } 
              else if(isenddateOnly)
            {
           
                 btn.textContent = 'Cancel';
                    btn.dataset.action = 'cancel';
                    btn.classList.add('cancel-btnn');
            } else {
            
                if (planAction === 'cancel') {
                    btn.textContent = 'Cancel';
                    btn.dataset.action = 'cancel';
                    btn.classList.add('cancel-btnn');
                } else {

                    btn.textContent = 'Upgrade';
                    btn.dataset.action = 'upgrade';
                    btn.classList.remove('cancel-btnn');
                }
            }
        });
    }

    updateButtons(monthlyclass);
    updateButtons(annualclass);

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

    if (activeSubscrInterval === 'Y') {
        showAnnual();
    } else {
        showMonthly();
    }

    toggle.addEventListener("change", () => {
        if (toggle.checked) showAnnual();
        else showMonthly();
    });

    // Show / Back buttons
    document.addEventListener('click', function (e) {

        if (e.target && e.target.id === 'showDetailsBtn') {
            document.getElementById('section1').style.display = 'none';
            document.getElementById('section2').style.display = 'block';
        }

        if (e.target && e.target.classList.contains('back-btn')) {
            document.getElementById('section2').style.display = 'none';
            document.getElementById('section1').style.display = 'block';
        }

    });

});