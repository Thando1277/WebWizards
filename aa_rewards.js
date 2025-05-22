<<<<<<< HEAD
let totalPoints = parseInt(localStorage.getItem('points') || '0');
let availablePoints = totalPoints;
const requiredPoints = 150;

const totalPointsDisplay = document.getElementById('points'); 
=======
let totalPoints = 0;
let availablePoints = 0;
const requiredPoints = 150;

const totalPointsDisplay = document.getElementById('total-points');
>>>>>>> 1f66006643f375b81051128c1fea7a69ceef946f
const availablePointsDisplay = document.getElementById('available-points');
const remainingPointsDisplay = document.getElementById('remaining-points');
const progressBar = document.getElementById('progress-bar');
const redeemBtn = document.getElementById('redeem-btn');
const redeemStatus = document.getElementById('redeem-status');
const historyList = document.getElementById('history');
<<<<<<< HEAD
=======
const earnBtn = document.getElementById('earn-btn');
>>>>>>> 1f66006643f375b81051128c1fea7a69ceef946f
const rewardSelect = document.getElementById('reward-select');

function updateUI() {
  totalPointsDisplay.textContent = totalPoints;
  availablePointsDisplay.textContent = availablePoints;
  const remaining = Math.max(requiredPoints - availablePoints, 0);
  remainingPointsDisplay.textContent = remaining;
  const progress = Math.min((availablePoints / requiredPoints) * 100, 100);
  progressBar.style.width = progress + '%';
  redeemBtn.disabled = availablePoints < requiredPoints;
<<<<<<< HEAD

  // Load and display history
  historyList.innerHTML = '';
  const history = JSON.parse(localStorage.getItem("rewardHistory") || "[]");
  history.forEach(item => {
    const li = document.createElement('li');
    li.textContent = item;
    historyList.appendChild(li);
  });
}

=======
}

earnBtn.addEventListener('click', () => {
  const earned = 20; 
  totalPoints += earned;
  availablePoints += earned;
  updateUI();
});

>>>>>>> 1f66006643f375b81051128c1fea7a69ceef946f
redeemBtn.addEventListener('click', () => {
  if (availablePoints >= requiredPoints) {
    const reward = rewardSelect.value === 'shoprite' ? 'Shoprite Voucher' : 'Airtime';
    availablePoints -= requiredPoints;
<<<<<<< HEAD
    totalPoints -= requiredPoints;
    localStorage.setItem('points', totalPoints.toString());

    const history = JSON.parse(localStorage.getItem("rewardHistory") || "[]");
    history.push(`🎁 ${reward} redeemed on ${new Date().toLocaleDateString()}`);
    localStorage.setItem("rewardHistory", JSON.stringify(history));

=======
    const li = document.createElement('li');
    li.textContent = `🎁 ${reward} redeemed on ${new Date().toLocaleDateString()}`;
    historyList.appendChild(li);
>>>>>>> 1f66006643f375b81051128c1fea7a69ceef946f
    redeemStatus.textContent = `✅ ${reward} successfully redeemed!`;
    updateUI();
  }
});

updateUI();
<<<<<<< HEAD



=======
>>>>>>> 1f66006643f375b81051128c1fea7a69ceef946f
