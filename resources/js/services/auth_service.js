import { http } from './http_service';

const roleMapping = {
    1: "admin",
    2: "vendor",
    3: "customer",
    4: "rider",
};

export function login(credentials) {
    return http()
        .get('/sanctum/csrf-cookie') // ✅ Ensure Laravel sets the CSRF cookie
        .then(() => http().post('/api/auth/login', credentials))
        .then(response => {
            if (response.data.token) {
                setAccessToken(response.data.token);
                const roleName = roleMapping[response.data.user.role_id]; // Map role_id to role name
                setUserRole(roleName);
                setUser(response.data.user);
            }
            return response;
        });
}
export function register(user) {
    return http().post('/api/auth/registerUser', user);
}
export function forgotPassword(user) {
    return http().post('/api/auth/forgot-request', user);
}
export function resetPassword(user) {
    return http().post('/api/auth/reset-password', user);
}
export function changePassword(user) {
    return http().post('/api/auth/change-password', user);
}
export function setAccessToken(token) {
    localStorage.setItem('access_token', token);
}

export function getAccessToken() {
    return localStorage.getItem('access_token');
}

export function clearAccessToken() {
    localStorage.removeItem('access_token');
}

export function setUserRole(role) {
    localStorage.setItem('user_role', role);
}

export function getUserRole() {
    return localStorage.getItem('user_role');
}

export function clearUserRole() {
    localStorage.removeItem('user_role');
}

export function setUser(user) {
    localStorage.setItem('user', JSON.stringify(user));
}

export function getUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
}

export function clearUser() {
    localStorage.removeItem('user');
}

export function isLoggedIn() {
    return !!getAccessToken();
}

export function redirectBasedOnRole() {
    const role = getUserRole();
    if (role) {
        router.push(`/${role}`);
    } else {
        router.push('/login');
    }
}

export function getProfile() {
    return http().get('/api/auth/profile').then((res) => res.data);
}

export function updateProfile(data) {
    return http().post('/api/auth/edit-Profile', data).then((res) => res.data);
}

export function logout() {
    function clearAllAuth() {
        clearAccessToken();
        clearUserRole();
        clearUser();
        try {
            localStorage.removeItem('auth_token');
        } catch (_) {}
    }

    return http()
        .get('/api/auth/logout')
        .then((response) => {
            clearAllAuth();
            window.location.href = '/login';
            return response;
        })
        .catch((error) => {
            console.error('Logout failed:', error);
            clearAllAuth();
            window.location.href = '/login';
            throw error;
        });
}
