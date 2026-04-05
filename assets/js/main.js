'use strict';

// Mobile menu
const mob = document.getElementById('mobToggle');
const menu = document.getElementById('mobMenu');
if(mob && menu){ mob.addEventListener('click', () => menu.classList.toggle('open')); }

// Bootstrap-style form validation
document.querySelectorAll('.vm-form').forEach(form => {
  form.addEventListener('submit', e => {
    if(!form.checkValidity()){ e.preventDefault(); e.stopPropagation(); }
    form.classList.add('was-validated');
  });
});

// Custom validation styles
document.querySelectorAll('.vm-input[required]').forEach(el => {
  el.addEventListener('blur', () => {
    el.style.borderColor = el.checkValidity() ? 'var(--border2)' : 'var(--orange)';
  });
  el.addEventListener('input', () => {
    if(el.classList.contains('was-touched')) el.style.borderColor = el.checkValidity() ? 'var(--border2)' : 'var(--orange)';
    el.classList.add('was-touched');
  });
});

// Password strength
const pwd = document.getElementById('password');
const bar = document.getElementById('pwdFill');
if(pwd && bar){
  pwd.addEventListener('input', () => {
    const v = pwd.value;
    let s = 0;
    if(v.length >= 8) s++;
    if(/[A-Z]/.test(v)) s++;
    if(/[0-9]/.test(v)) s++;
    if(/[^A-Za-z0-9]/.test(v)) s++;
    bar.style.width = (s/4*100)+'%';
    bar.style.background = ['','var(--orange)','#fbbf24','var(--blue)','var(--accent)'][s];
  });
}

// Confirm password
const cpwd = document.getElementById('confirm_password');
if(pwd && cpwd){
  const check = () => cpwd.setCustomValidity(pwd.value !== cpwd.value ? 'Passwords do not match.' : '');
  pwd.addEventListener('change', check);
  cpwd.addEventListener('input', check);
}

// Qty buttons
document.querySelectorAll('[data-qty]').forEach(btn => {
  btn.addEventListener('click', () => {
    const inp = document.querySelector(btn.dataset.target);
    if(!inp) return;
    let v = parseInt(inp.value)||1;
    const max = parseInt(inp.max)||999;
    if(btn.dataset.qty === 'up') v = Math.min(v+1, max);
    if(btn.dataset.qty === 'dn') v = Math.max(v-1, 1);
    inp.value = v;
  });
});

// Auto-dismiss alerts
document.querySelectorAll('.vm-alert[data-dismiss]').forEach(el => {
  setTimeout(() => { el.style.opacity='0'; el.style.transition='opacity .4s'; setTimeout(()=>el.remove(),400); }, 4000);
});

// Star rating
const stars = document.querySelectorAll('.rating-star');
const ratingVal = document.getElementById('ratingValue');
stars.forEach(s => {
  s.addEventListener('click', () => { if(ratingVal) ratingVal.value = s.dataset.v; highlightStars(parseInt(s.dataset.v)); });
  s.addEventListener('mouseenter', () => highlightStars(parseInt(s.dataset.v)));
});
document.querySelector('.stars-row')?.addEventListener('mouseleave', () => {
  highlightStars(parseInt(ratingVal?.value)||0);
});
function highlightStars(n){
  stars.forEach(s => { const v = parseInt(s.dataset.v); s.style.color = v <= n ? '#fbbf24' : 'var(--border2)'; });
}

// Price range label
const priceRange = document.getElementById('priceRange');
const priceLabel = document.getElementById('priceLabel');
if(priceRange && priceLabel){
  priceRange.addEventListener('input', () => priceLabel.textContent = '$'+priceRange.value);
}

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => { if(!confirm(el.dataset.confirm)) e.preventDefault(); });
});

// Product image gallery
document.querySelectorAll('.thumb-img').forEach(img => {
  img.addEventListener('click', () => {
    document.getElementById('mainImg').src = img.src;
    document.querySelectorAll('.thumb-img').forEach(t => t.style.borderColor='var(--border2)');
    img.style.borderColor = 'var(--accent)';
  });
});
