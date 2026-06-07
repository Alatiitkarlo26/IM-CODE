// Wait for the DOM to fully load
document.addEventListener('DOMContentLoaded', () => {
  
  // Element Selectors
  const followBtn = document.getElementById('btn-follow');
  const messageBtn = document.getElementById('btn-message');
  const followerCountEl = document.getElementById('follower-count');
  
  // Initial state variables
  let isFollowing = false;
  let numericFollowers = 1250;

  // 1. Follow Button Logic
  followBtn.addEventListener('click', () => {
    isFollowing = !isFollowing; // Toggle state

    if (isFollowing) {
      numericFollowers++;
      followBtn.textContent = 'Following ✓';
      followBtn.classList.add('following');
    } else {
      numericFollowers--;
      followBtn.textContent = 'Follow';
      followBtn.classList.remove('following');
    }

    // Format number with commas (e.g., 1,251) and update DOM
    followerCountEl.textContent = numericFollowers.toLocaleString();
  });

  // 2. Message Button Logic
  messageBtn.addEventListener('click', () => {
    alert('Opening a direct message conversation with Jane Doe...');
  });

});