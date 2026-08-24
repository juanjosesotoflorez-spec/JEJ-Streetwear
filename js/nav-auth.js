// Fetch session status and update any nav links that show login/register
(function(){
    async function updateNav() {
        try {
            const res = await fetch('/php/auth/session_status.php', {credentials: 'same-origin'});
            if (!res.ok) return;
            const data = await res.json();
            const nav = document.querySelector('.navbar-links');
            if (!nav) return;

            // Find login and register links
            const loginLink = Array.from(nav.querySelectorAll('a')).find(a => /login\.php|Iniciar Sesión|Iniciar sesión/i.test(a.textContent) || /login\.php/i.test(a.getAttribute('href')) );
            const registerLink = Array.from(nav.querySelectorAll('a')).find(a => /register\.php|Regístrate|Registrarse/i.test(a.textContent) || /register\.php/i.test(a.getAttribute('href')) );

            if (data.logged) {
                // Create account and logout links
                const accountLi = document.createElement('li');
                const accountA = document.createElement('a');
                accountA.href = data.role === 'admin' ? '/php/admin/dashboard.php' : '/php/user/dashboard.php';
                accountA.innerHTML = '<i class="fas fa-user-circle"></i> Ver mi cuenta';
                accountLi.appendChild(accountA);

                const logoutLi = document.createElement('li');
                const logoutA = document.createElement('a');
                logoutA.href = '/php/auth/logout.php';
                logoutA.innerHTML = '<i class="fas fa-sign-out-alt"></i> Cerrar Sesión';
                logoutLi.appendChild(logoutA);

                // Replace or insert
                if (loginLink) {
                    const loginLi = loginLink.closest('li');
                    if (loginLi) loginLi.parentNode.replaceChild(accountLi, loginLi);
                    else nav.insertBefore(accountLi, nav.firstChild);
                } else {
                    nav.insertBefore(accountLi, nav.firstChild);
                }

                if (registerLink) {
                    const regLi = registerLink.closest('li');
                    if (regLi) regLi.parentNode.replaceChild(logoutLi, regLi);
                    else nav.appendChild(logoutLi);
                } else {
                    nav.appendChild(logoutLi);
                }

            } else {
                // Not logged: ensure login/register exist (no-op if already present)
                // If loginLink is missing, add one
                if (!loginLink) {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = '/php/auth/login.php';
                    a.innerHTML = '<i class="fas fa-user"></i> Iniciar Sesión';
                    li.appendChild(a);
                    nav.insertBefore(li, nav.firstChild);
                }
                if (!registerLink) {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = '/php/auth/register.php';
                    a.innerHTML = '<i class="fas fa-user-plus"></i> Regístrate';
                    li.appendChild(a);
                    // place after login
                    const first = nav.querySelector('li');
                    if (first) first.parentNode.insertBefore(li, first.nextSibling);
                    else nav.appendChild(li);
                }
            }
        } catch (e) {
            // fail silently
            console.error('nav-auth error', e);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateNav);
    } else {
        updateNav();
    }
})();
