import { useAuth } from '@/stores/auth';

/**
 * Route guards decide using the logged user, so it must be known before they run.
 */
export default () => useAuth().loadIdentity();
