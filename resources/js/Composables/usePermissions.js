import { usePage } from '@inertiajs/vue3';

/**
 * Permission and Role checking composable
 * 
 * Provides helper functions to check user permissions and roles
 * in Vue components using Laratrust permissions system
 */
export function usePermissions() {
    const page = usePage();

    /**
     * Check if user has a specific permission
     * @param {string} permission - Permission name to check
     * @returns {boolean}
     */
    const can = (permission) => {
        const permissions = page.props.auth?.user?.permissions || [];
        return permissions.includes(permission);
    };

    /**
     * Check if user has any of the specified permissions
     * @param {Array<string>} permissions - Array of permission names
     * @returns {boolean}
     */
    const canAny = (permissions) => {
        if (!Array.isArray(permissions)) {
            console.warn('canAny expects an array of permissions');
            return false;
        }
        return permissions.some(permission => can(permission));
    };

    /**
     * Check if user has all of the specified permissions
     * @param {Array<string>} permissions - Array of permission names
     * @returns {boolean}
     */
    const canAll = (permissions) => {
        if (!Array.isArray(permissions)) {
            console.warn('canAll expects an array of permissions');
            return false;
        }
        return permissions.every(permission => can(permission));
    };

    /**
     * Check if user has a specific role
     * @param {string} role - Role name to check
     * @returns {boolean}
     */
    const hasRole = (role) => {
        const roles = page.props.auth?.user?.roles || [];
        return roles.includes(role);
    };

    /**
     * Check if user has any of the specified roles
     * @param {Array<string>} roles - Array of role names
     * @returns {boolean}
     */
    const hasAnyRole = (roles) => {
        if (!Array.isArray(roles)) {
            console.warn('hasAnyRole expects an array of roles');
            return false;
        }
        return roles.some(role => hasRole(role));
    };

    /**
     * Check if user has all of the specified roles
     * @param {Array<string>} roles - Array of role names
     * @returns {boolean}
     */
    const hasAllRoles = (roles) => {
        if (!Array.isArray(roles)) {
            console.warn('hasAllRoles expects an array of roles');
            return false;
        }
        return roles.every(role => hasRole(role));
    };

    /**
     * Check if user is admin
     * @returns {boolean}
     */
    const isAdmin = () => {
        return hasRole('admin');
    };

    /**
     * Get current user's roles
     * @returns {Array<string>}
     */
    const getUserRoles = () => {
        return page.props.auth?.user?.roles || [];
    };

    /**
     * Get current user's permissions
     * @returns {Array<string>}
     */
    const getUserPermissions = () => {
        return page.props.auth?.user?.permissions || [];
    };

    return {
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
        hasAllRoles,
        isAdmin,
        getUserRoles,
        getUserPermissions,
    };
}
