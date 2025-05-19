 let totalPoints = 0;
    let availablePoints = 0;
    const requiredPoints = 150;

    const totalPointsDisplay = document.getElementById('total-points');
    const availablePointsDisplay = document.getElementById('available-points');
    const remainingPointsDisplay = document.getElementById('remaining-points');
    const progressBar = document.getElementById('progress-bar');
    const redeemBtn = document.getElementById("submit");
    const redeemStatus = document.getElementById('redeem-status');
    const historyList = document.getElementById('history');
    const earnBtn = document.getElementById('earn-btn');
    const rewardSelect = document.getElementById('reward-select');

    function updateUI() {
      totalPointsDisplay.textContent = totalPoints;
      availablePointsDisplay.textContent = availablePoints;
      const remaining = Math.max(requiredPoints - availablePoints, 0);
      remainingPointsDisplay.textContent = remaining;
      const progress = Math.min((availablePoints / requiredPoints) * 100, 100);
      progressBar.style.width = progress + '%';
      redeemBtn.disabled = availablePoints < requiredPoints;
    }

    earnBtn.addEventListener('click', () => {
      const earned = 20; 
      totalPoints += earned;
      availablePoints += earned;
      updateUI();
    });

    redeemBtn.addEventListener('click', () => {
      if (availablePoints >= requiredPoints) {
        const reward = rewardSelect.value === 'shoprite' ? 'Shoprite Voucher' : 'Airtime';
        availablePoints -= requiredPoints;
        const li = document.createElement('li');
        li.textContent = `🎁 ${reward} redeemed on ${new Date().toLocaleDateString()}`;
        historyList.appendChild(li);
        redeemStatus.textContent = `✅ ${reward} successfully redeemed!`;
        updateUI();
      }
    });

    updateUI();

    