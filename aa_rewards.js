const config = {
  pointsPerReport: 20,
  pointsRequiredForReward: 150
};

const state = {
  totalPoints: 0,
  availablePoints: 0
};

document.addEventListener('DOMContentLoaded', () => {
  // Initialize the page
  fetchPointsAndUpdateUI();

  // Back button functionality
  document.getElementById('BackBtn').addEventListener('click', () => {
    window.location.href = 'premium-dashboard.html';
  });

  document.getElementById('redeem-btn').addEventListener('click', redeemReward);
});

function showNotification(message, isError = false) {
  const notification = document.createElement('div');
  notification.className = `notification ${isError ? 'error' : 'success'}`;
  notification.innerHTML = message.replace(/\n/g, '<br>');
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 3000);
}

function updateUI() {
  // Update points display
  document.getElementById('total-points').textContent = state.totalPoints;
  document.getElementById('available-points').textContent = state.availablePoints;

  // Calculate and display remaining points needed
  const remainingPoints = Math.max(0, config.pointsRequiredForReward - state.availablePoints);
  document.getElementById('remaining-points').textContent = remainingPoints;

  // Update progress bar
  const progressPercent = Math.min(100, (state.availablePoints / config.pointsRequiredForReward) * 100);
  document.getElementById('progress-bar').style.width = `${progressPercent}%`;

  // Enable/disable redeem button
  const redeemBtn = document.getElementById('redeem-btn');
  redeemBtn.disabled = state.availablePoints < config.pointsRequiredForReward;
}

function fetchPointsAndUpdateUI() {
  fetch('get_points.php')
    .then(res => {
      if (!res.ok) throw new Error('Network response was not ok');
      return res.json();
    })
    .then(data => {
      if (data.error) {
        showNotification(data.error, true);
        return;
      }

      state.totalPoints = data.TotalPoints || 0;
      state.availablePoints = data.AvailablePoints || 0;

      // Update history list only:
      const historyList = document.getElementById('history');
      historyList.innerHTML = '';

      if (data.history && data.history.length > 0) {
        data.history.forEach(item => {
          const historyItem = document.createElement('li');
          historyItem.className = 'history-item';

          const date = new Date(item.RedeemedAt);
          const formattedDate = date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });

          historyItem.innerHTML = `
            <span class="reward-type">${item.RewardType}</span>
            <span class="reward-points">${item.PointsUsed} points</span>
            <span class="reward-date">${formattedDate}</span>
          `;
          historyList.appendChild(historyItem);
        });
      } else {
        historyList.innerHTML = '<li class="no-history">No rewards redeemed yet</li>';
      }

      updateUI();
    })
    .catch(error => {
      showNotification('Failed to load history: ' + error.message, true);
      console.error('Error:', error);
    });
}

// Generates a random Airtime reward
function generateAirtime() {
  const pin = Array.from({ length: 16 }, () => Math.floor(Math.random() * 10)).join('');
  return {
    type: 'Airtime Top-Up',
    value: 'R10',
    pin: pin.match(/.{1,4}/g).join('-'),
    expiry: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toLocaleDateString()
  };
}

// Generates a random Shoprite voucher
function generateShopriteVoucher() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';
  const code = Array.from({ length: 12 }, () => chars.charAt(Math.floor(Math.random() * chars.length))).join('');
  return {
    type: "Shoprite Voucher",
    value: "R100",
    code: code.match(/.{1,4}/g).join('-'),
    expiry: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toLocaleDateString()
  };
}

function redeemReward() {
  const rewardSelect = document.getElementById('reward-select');
  const selectedReward = rewardSelect.value.toString().trim();
  const redeemBtn = document.getElementById('redeem-btn');

  redeemBtn.disabled = true;
  redeemBtn.textContent = 'Processing...';

  setTimeout(() => {
    fetch('redeem_reward.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reward: selectedReward })
    })
      .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
      })
      .then(data => {
        let rewardDetails;
        let notificationMessage = '';

        if (data.success) {
          if (selectedReward === 'airtime') {
            rewardDetails = generateAirtime();
            notificationMessage = `${data.message || 'Airtime redeemed!'}<br>
              <strong>PIN:</strong> ${rewardDetails.pin}<br>
              <strong>Expires:</strong> ${rewardDetails.expiry}`;
          } else if (selectedReward === 'shoprite') {
            rewardDetails = generateShopriteVoucher();
            notificationMessage = `${data.message || 'Voucher redeemed!'}<br>
              <strong>Voucher:</strong> ${rewardDetails.code}<br>
              <strong>Expires:</strong> ${rewardDetails.expiry}`;
          }

          document.getElementById('reward-content').innerHTML = notificationMessage;
          showNotification(data.message || 'Reward redeemed!');
          fetchPointsAndUpdateUI();
        } else {
          const errorMsg = data.message || '❌ Failed to redeem reward';
          showNotification(errorMsg, true);
          document.getElementById('reward-content').innerHTML = `<span class="error">${errorMsg}</span>`;
        }
      })
      .catch(error => {
        const errorMessage = '❌ Network error: ' + error.message;
        showNotification(errorMessage, true);
        document.getElementById('reward-content').innerHTML = `<span class="error">${errorMessage}</span>`;
        console.error('Error:', error);
      })
      .finally(() => {
        redeemBtn.textContent = 'Redeem';
        redeemBtn.disabled = state.availablePoints < config.pointsRequiredForReward;
      });
  }, 1500);
}

function awardPoints() {
  fetch('award_points.php', { method: 'POST' })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showNotification(`+${config.pointsPerReport} points!`);
        fetchPointsAndUpdateUI();
      } else {
        showNotification(`${data.error || 'Failed to award points'}`, true);
      }
    })
    .catch(error => {
      showNotification('Network error', true);
      console.error('Error:', error);
    });
}
