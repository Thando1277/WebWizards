let totalPoints = parseInt(localStorage.getItem('points') || '0');
let availablePoints = totalPoints;
const requiredPoints = 150;

const totalPointsDisplay = document.getElementById('points'); 
const availablePointsDisplay = document.getElementById('available-points');
const remainingPointsDisplay = document.getElementById('remaining-points');
const progressBar = document.getElementById('progress-bar');
const redeemBtn = document.getElementById('redeem-btn');
const redeemStatus = document.getElementById('redeem-status');
const historyList = document.getElementById('history');
const rewardSelect = document.getElementById('reward-select');

function updateUI() {
  totalPointsDisplay.textContent = totalPoints;
  availablePointsDisplay.textContent = availablePoints;
  const remaining = Math.max(requiredPoints - availablePoints, 0);
  remainingPointsDisplay.textContent = remaining;
  const progress = Math.min((availablePoints / requiredPoints) * 100, 100);
  progressBar.style.width = progress + '%';
  redeemBtn.disabled = availablePoints < requiredPoints;

  // Load and display history
  historyList.innerHTML = '';
  const history = JSON.parse(localStorage.getItem("rewardHistory") || "[]");
  history.forEach(item => {
    const li = document.createElement('li');
    li.textContent = item;
    historyList.appendChild(li);
  });
}

redeemBtn.addEventListener('click', () => {
  if (availablePoints >= requiredPoints) {
    const reward = rewardSelect.value === 'shoprite' ? 'Shoprite Voucher' : 'Airtime';
    availablePoints -= requiredPoints;
    totalPoints -= requiredPoints;
    localStorage.setItem('points', totalPoints.toString());

    const history = JSON.parse(localStorage.getItem("rewardHistory") || "[]");
    history.push(`🎁 ${reward} redeemed on ${new Date().toLocaleDateString()}`);
    localStorage.setItem("rewardHistory", JSON.stringify(history));

    redeemStatus.textContent = `✅ ${reward} successfully redeemed!`;
    updateUI();
  }
});

updateUI();



