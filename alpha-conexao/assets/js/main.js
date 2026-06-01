/* =============================================
   ALPHA CONEXÃO – JavaScript Principal
   ============================================= */

// Sidebar mobile toggle
(function () {
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('acSidebar');
  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });

  // Fecha sidebar ao clicar fora
  document.addEventListener('click', (e) => {
    if (window.innerWidth > 768) return;
    if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
})();

// Dropdown de usuário
(function () {
  const btn      = document.getElementById('userMenuBtn');
  const dropdown = document.getElementById('userDropdown');
  if (!btn || !dropdown) return;

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('show');
  });

  document.addEventListener('click', () => dropdown.classList.remove('show'));
})();

// Mostra/esconde senha
function togglePassword(fieldId) {
  const field = document.getElementById(fieldId);
  const icon  = document.getElementById('eye' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1));
  if (!field) return;
  if (field.type === 'password') {
    field.type = 'text';
    if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
  } else {
    field.type = 'password';
    if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
  }
}

// Máscara de CNPJ
function maskCnpj(value) {
  return value
    .replace(/\D/g, '')
    .slice(0, 14)
    .replace(/^(\d{2})(\d)/, '$1.$2')
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/\.(\d{3})(\d)/, '.$1/$2')
    .replace(/(\d{4})(\d)/, '$1-$2');
}

// Aplica máscara em campos CNPJ
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[name="cnpj"], #cnpj, #quickCnpj').forEach(el => {
    el.addEventListener('input', () => { el.value = maskCnpj(el.value); });
  });

  // Indicador de força de senha
  const senhaInput = document.getElementById('senha');
  const bar        = document.getElementById('strengthBar');
  if (senhaInput && bar) {
    senhaInput.addEventListener('input', () => {
      const v   = senhaInput.value;
      let score = 0;
      if (v.length >= 8)  score++;
      if (/[A-Z]/.test(v))score++;
      if (/[0-9]/.test(v))score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;
      const colors = ['#EF4444','#F59E0B','#3B82F6','#22C55E'];
      const widths  = ['25%','50%','75%','100%'];
      bar.style.width      = score > 0 ? widths[score - 1] : '0';
      bar.style.background = score > 0 ? colors[score - 1] : '';
    });
  }

  // Botão com loading no form de consulta CNPJ
  const cnpjForm = document.getElementById('cnpjForm');
  if (cnpjForm) {
    cnpjForm.addEventListener('submit', () => {
      const btn  = document.getElementById('btnConsultar');
      const icon = document.getElementById('btnIcon');
      const text = document.getElementById('btnText');
      if (btn) btn.disabled = true;
      if (icon) { icon.classList.remove('fa-search'); icon.classList.add('fa-spinner', 'fa-spin'); }
      if (text) text.textContent = 'Consultando...';
    });
  }

  // Fecha alertas automaticamente após 6s
  document.querySelectorAll('.ac-alert').forEach(alert => {
    setTimeout(() => {
      if (alert.parentElement) {
        alert.style.transition = 'opacity .4s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 400);
      }
    }, 6000);
  });

  // Sincroniza color picker com input text
  document.querySelectorAll('.ac-color-input').forEach(picker => {
    const textInput = picker.nextElementSibling;
    if (!textInput) return;
    picker.addEventListener('input', () => { textInput.value = picker.value; });
    textInput.addEventListener('input', () => {
      if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) picker.value = textInput.value;
    });
  });
});

// Validação de CNPJ no frontend
function validateCnpj(cnpj) {
  cnpj = cnpj.replace(/\D/g, '');
  if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
  let sum = 0, pos = 5;
  for (let i = 0; i < 12; i++) { sum += parseInt(cnpj[i]) * pos--; if (pos < 2) pos = 9; }
  let r = sum % 11 < 2 ? 0 : 11 - (sum % 11);
  if (r !== parseInt(cnpj[12])) return false;
  sum = 0; pos = 6;
  for (let i = 0; i < 13; i++) { sum += parseInt(cnpj[i]) * pos--; if (pos < 2) pos = 9; }
  r = sum % 11 < 2 ? 0 : 11 - (sum % 11);
  return r === parseInt(cnpj[13]);
}

// Feedback visual de CNPJ digitado
document.addEventListener('DOMContentLoaded', () => {
  const cnpjInput = document.getElementById('cnpj');
  const status    = document.getElementById('cnpjStatus');
  if (!cnpjInput || !status) return;

  cnpjInput.addEventListener('input', () => {
    const raw = cnpjInput.value.replace(/\D/g, '');
    if (raw.length === 14) {
      if (validateCnpj(raw)) {
        status.textContent = '✓ CNPJ válido';
        status.style.color = '#16A34A';
      } else {
        status.textContent = '✗ CNPJ inválido';
        status.style.color = '#DC2626';
      }
    } else {
      status.textContent = '';
    }
  });
});
