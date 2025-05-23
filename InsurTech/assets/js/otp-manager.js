/**
 * OTP Manager - Handles all OTP-related functionality
 * Includes fetching, displaying, and copying OTPs
 */

// Global variables
const COMPANY_DATA = {
  'pb': {
    id: 1,
    name: 'PolicyBazaar',
    containerId: 'pb-otp-container',
    refreshEndpoint: 'otp/fetch_pb_otp.php',
    getEndpoint: 'otp/get_pb_otps.php',
    colorClass: 'primary'
  },
  'icici': {
    id: 2,
    name: 'ICICI Lombard',
    containerId: 'icici-otp-container',
    refreshEndpoint: 'otp/fetch_icici_otp.php',
    getEndpoint: 'otp/get_icici_otps.php',
    colorClass: 'danger'
  },
  'tataaig': {
    id: 3,
    name: 'TATA AIG',
    containerId: 'tata-aig-container',
    refreshEndpoint: 'otp/fetch_tataaig_otp.php',
    getEndpoint: 'otp/get_tataaig_otps.php',
    colorClass: 'primary'
  }
};

let refreshTimer = null;
let lastOtpCounts = { pb: 0, icici: 0, tataaig: 0 };
let refreshCountdown = 30;
let countdownTimer = null;
let isLoading = false;

// Initialize the OTP management system
document.addEventListener('DOMContentLoaded', function() {
  // Load all OTPs on page load
  loadAllOtps();
  
  // Set up auto-refresh
  setupAutoRefresh();
  
  // Initialize tooltips
  if (typeof coreui !== 'undefined' && coreui.Tooltip) {
    document.body.addEventListener('mouseover', function(e) {
      const tooltipTriggers = document.querySelectorAll('[data-coreui-toggle="tooltip"]');
      [...tooltipTriggers].forEach(el => new coreui.Tooltip(el));
    }, { once: true });
  }

  // Start the countdown display
  updateLastRefreshTime();
  startCountdownTimer();
});

// Load OTPs for all companies
function loadAllOtps() {
  if (isLoading) return;
  
  isLoading = true;
  
  const promises = [
    loadOtpList('pb'),
    loadOtpList('icici'),
    loadOtpList('tataaig')
  ];
  
  // Use Promise.all with a finally handler to ensure loader is hidden
  Promise.all(promises.map(p => p.catch(err => {
    console.error("Error in loadOtpList:", err);
    return null; // Convert rejection to resolved null to prevent Promise.all from failing early
  })))
  .finally(() => {
    updateLastRefreshTime();
    isLoading = false;
  });
}

// Update the last refresh time display
function updateLastRefreshTime() {
  const now = new Date();
  const timeString = now.toLocaleTimeString();
  const element = document.getElementById('last-refresh-time');
  if (element) {
    element.textContent = timeString;
  }
}

// Start countdown timer display
function startCountdownTimer() {
  // Clear existing timer if it exists
  if (countdownTimer) {
    clearInterval(countdownTimer);
  }
  
  refreshCountdown = 30;
  updateCountdownDisplay();
  
  countdownTimer = setInterval(() => {
    refreshCountdown--;
    updateCountdownDisplay();
    
    if (refreshCountdown <= 0) {
      refreshCountdown = 30;
    }
  }, 1000);
}

// Update the countdown display
function updateCountdownDisplay() {
  const element = document.getElementById('next-refresh-time');
  if (element) {
    element.textContent = `${refreshCountdown}s`;
  }
}

// Set up auto-refresh
function setupAutoRefresh() {
  // Clear existing timer
  if (refreshTimer !== null) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
  
  // Set new timer - refresh every 30 seconds
  refreshTimer = setInterval(() => {
    /*loadAllOtps();*/
    loadNewOtpList('pb');
    loadNewOtpList('icici');
    loadNewOtpList('tataaig');
    refreshCountdown = 30; // Reset countdown
  }, 30000);
}

// Load OTP list for specific company
function loadNewOtpList(companyCode) {
  const company = COMPANY_DATA[companyCode];
  if (!company) return Promise.reject(new Error('Invalid company code'));
  
  const container = document.getElementById(company.containerId);
  if (!container) return Promise.reject(new Error(`Container not found: ${company.containerId}`));
  
  fetch(company.refreshEndpoint)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Response is not JSON. Please check server configuration.');
      }
      return response.json();
    })
    .then(data => {
      if (data.status === "success") {
        // Always reload the OTP list to show updated data
        loadOtpList(companyCode);
      } else {
        // Show error message
        showAlert(`Error: ${data.message || 'Unknown error'}`, 'danger');
      }
    })
    .catch(err => {
      console.error(`${company.name} OTP Refresh Error:`, err);
      showAlert(`Failed to refresh ${company.name} OTPs: ${err.message}`, 'danger');
      
      // Try to reload the OTP list anyway to recover from errors
      try {
        loadOtpList(companyCode);
      } catch (e) {
        console.error("Failed to reload OTP list after error:", e);
      }
    })
}

// Load OTP list for specific company
function loadOtpList(companyCode) {
  const company = COMPANY_DATA[companyCode];
  if (!company) return Promise.reject(new Error('Invalid company code'));
  
  const container = document.getElementById(company.containerId);
  if (!container) return Promise.reject(new Error(`Container not found: ${company.containerId}`));
  
  return fetch(company.getEndpoint)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Response is not JSON. Please check server configuration.');
      }
      return response.json();
    })
    .then(data => {
      container.innerHTML = "";
      
      if (data.status === "success" && Array.isArray(data.data) && data.data.length > 0) {
        // Check if there are new OTPs
        const newOtpCount = data.data.length;
        if (lastOtpCounts[companyCode] > 0 && newOtpCount > lastOtpCounts[companyCode]) {
          notifyNewOtp(company.name);
        }
        lastOtpCounts[companyCode] = newOtpCount;
        
        // Display only the last 5 OTPs
        const latestOtps = data.data.slice(0, 5);
        
        latestOtps.forEach(otp => {
          const card = createOtpCard(otp, company);
          container.appendChild(card);
        });
      } else {
        container.innerHTML = `<div class="text-center text-muted small">No OTPs found.</div>`;
      }
      return data;
    })
    .catch(err => {
      console.error(`${company.name} OTP Load Error:`, err);
      container.innerHTML = `<div class="alert alert-danger p-1 small">Failed to load OTPs: ${err.message}</div>`;
      showAlert(`Failed to load ${company.name} OTPs: ${err.message}`, 'danger');
      return Promise.reject(err);
    });
}

// Format date/time in a more readable format
function formatDateTime(dateTimeStr) {
  if (!dateTimeStr) return '';
  
  const date = new Date(dateTimeStr);
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
}

// Create OTP card element - updated with OTP time and user info
function createOtpCard(otp, company) {
  const div = document.createElement("div");
  const isViewed = otp.viewed === "1" || otp.viewed === 1;
  
  // Add classes to indicate viewed status
  div.className = `card border-${company.colorClass} mb-1 ${isViewed ? 'bg-light' : ''}`;
  
  const timeAgo = getTimeAgo(otp.created_time);
  const maskedOtp = isViewed ? otp.otp : maskOtp(otp.otp);
  
  // Format creation and viewed times
  const createdTime = formatDateTime(otp.created_time);
  const viewedTime = isViewed ? formatDateTime(otp.viewed_time) : '';
  
  div.innerHTML = `
  <div class="card-body p-2">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex flex-column">
        <div class="d-flex align-items-center gap-1">
          <span id="otp_${company.id}_${otp.id}" class="fw-bold fs-6">${maskedOtp}</span>
          <small class="text-muted">${timeAgo}</small>
          ${isViewed ? '<span class="badge bg-secondary ms-1 small">Viewed</span>' : ''}
        </div>
        <div class="small text-muted">${createdTime}</div>
        ${isViewed && otp.viewed_by ? 
          `<div class="small text-muted">Viewed by: ${otp.viewed_by} at ${viewedTime}</div>` : ''}
      </div>
      ${!isViewed ? `
        <button class="btn btn-sm btn-outline-${company.colorClass} py-0 px-2" 
          onclick="copyOtpToClipboard('${otp.otp}', ${otp.id}, ${company.id}, '${company.name}')">
          <i class="fa fa-copy"></i>
        </button>` : ''}
    </div>
  </div>
`;
  
  return div;
}

// Copy OTP to clipboard and reveal it
function copyOtpToClipboard(otp, id, companyId, companyName) {
  // First reveal the OTP
  const otpSpan = document.getElementById(`otp_${companyId}_${id}`);
  if (otpSpan) {
    otpSpan.textContent = otp;
  }
  
  // Copy to clipboard
  navigator.clipboard.writeText(otp)
    .then(() => {
      // Show temporary copy confirmation
      showAlert(`OTP ${otp} copied to clipboard!`, 'success');
      
      // Log OTP access
      logOtpAccess(id, companyId, companyName, otp)
        .then(() => {
          // Reload the OTPs to show updated viewed status
          for (const code in COMPANY_DATA) {
            if (COMPANY_DATA[code].id === companyId) {
              loadOtpList(code);
              break;
            }
          }
        });
    })
    .catch(err => {
      console.error('Copy failed:', err);
      showAlert(`Failed to copy OTP: ${err.message}`, 'danger');
    });
}

// Show alert message
function showAlert(message, type = 'success') {
  const accessAlert = document.getElementById('access-alert');
  const accessMessage = document.getElementById('access-message');
  
  if (!accessAlert || !accessMessage) return;
  
  accessMessage.textContent = message;
  accessAlert.className = `alert alert-${type} alert-dismissible fade show`;
  accessAlert.classList.remove('d-none');
  
  // Auto hide after 3 seconds
  setTimeout(() => {
    accessAlert.classList.add('d-none');
  }, 3000);
}

// Log OTP access to database
function logOtpAccess(id, companyId, companyName, otp) {
  return fetch('otp/otp_view_log.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `otp_id=${id}&company_id=${companyId}`
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.status !== "success") {
      console.error("OTP access logging failed:", data.message || "Unknown error");
      return Promise.reject(new Error(data.message || "Unknown error"));
    }
    return data;
  })
  .catch(err => {
    console.error("OTP Access Log Error:", err);
    return Promise.reject(err);
  });
}

// Refresh OTPs manually for a specific company
function refreshOtpManually(companyCode) {
  const company = COMPANY_DATA[companyCode];
  if (!company) return;
  
  // Prevent default action and stop event propagation
  event.preventDefault();
  event.stopPropagation();
  
  // Disable the refresh button to prevent multiple clicks
  const refreshBtn = document.getElementById(`refresh-${companyCode}-btn`);
  if (refreshBtn) {
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Refreshing...';
  }
  
  fetch(company.refreshEndpoint)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Response is not JSON. Please check server configuration.');
      }
      return response.json();
    })
    .then(data => {
      if (data.status === "success") {
        // Always reload the OTP list to show updated data
        loadOtpList(companyCode);
        
        // Show appropriate message
        if (data.otp) {
          showAlert(`${company.name} OTP refreshed successfully: ${data.message}`, 'success');
        } else {
          showAlert(data.message || `${company.name} OTPs checked.`, 'success');
        }
      } else {
        // Show error message
        showAlert(`Error: ${data.message || 'Unknown error'}`, 'danger');
      }
    })
    .catch(err => {
      console.error(`${company.name} OTP Refresh Error:`, err);
      showAlert(`Failed to refresh ${company.name} OTPs: ${err.message}`, 'danger');
      
      // Try to reload the OTP list anyway to recover from errors
      try {
        loadOtpList(companyCode);
      } catch (e) {
        console.error("Failed to reload OTP list after error:", e);
      }
    })
    .finally(() => {
      // Re-enable the refresh button
      if (refreshBtn) {
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = '<i class="fa fa-sync-alt"></i> Refresh';
      }
    });
}

// Notify user of new OTP
function notifyNewOtp(companyName) {
  // Show notification
  showAlert(`New ${companyName} OTP available!`, 'success');
  
  // Try to play notification sound if available
  try {
    const notificationSound = new Audio('assets/sounds/notification.mp3');
    notificationSound.play().catch(e => console.error("Sound play error:", e));
  } catch (err) {
    console.warn("Sound notification not supported:", err);
  }
}

// Mask OTP for security - show only last 2 digits
function maskOtp(otp) {
  if (!otp) return '******';
  
  const length = otp.length;
  if (length <= 2) return otp;
  
  const maskedPart = '*'.repeat(length - 2);
  const visiblePart = otp.substring(length - 2);
  
  return maskedPart + visiblePart;
}

// Get time ago string from timestamp
function getTimeAgo(timestamp) {
  if (!timestamp) return '';
  
  const date = new Date(timestamp);
  const now = new Date();
  const diffMs = now - date;
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  
  if (diffSec < 60) {
    return `${diffSec}s ago`;
  } else if (diffMin < 60) {
    return `${diffMin}m ago`;
  } else if (diffHour < 24) {
    return `${diffHour}h ago`;
  } else {
    return date.toLocaleDateString();
  }
}