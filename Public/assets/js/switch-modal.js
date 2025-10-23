const wrapper = document.getElementById('authWrapper');
const switchBtn = document.getElementById('switchBtn');
const panelTitle = document.getElementById('panelTitle');
const panelText = document.getElementById('panelText');

let isLogin = true;

switchBtn.addEventListener('click', () => {
  wrapper.classList.toggle('active');
  isLogin = !isLogin;
  
  if (isLogin) {
    switchBtn.textContent = 'Register';
    panelTitle.textContent = "Don’t have an account?";
    panelText.textContent = "Sign up and start your Drip journey!";
  } else {
    switchBtn.textContent = 'Login';
    panelTitle.textContent = "Already have an account?";
    panelText.textContent = "Login and continue shopping!";
  }
});