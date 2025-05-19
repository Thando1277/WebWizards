let points = 0;
const pointsDisplay = document.getElementById('points');
const progressBar = document.getElementById('progress');
const activityLog = document.getElementById('activityLog');

document.getElementById('submitBtn').addEventListener('click', () => {
  const gainedPoints = 500;
  points += gainedPoints;
  pointsDisplay.textContent = points;

 
  const log = document.createElement('p');
  log.textContent = `+${gainedPoints} points from referral!`;
  activityLog.prepend(log);


  let percent = Math.min((points % 5000) / 50, 100);
  progressBar.style.width = percent + '%';
});
