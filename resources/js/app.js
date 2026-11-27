import Alpine from 'alpinejs';
import { startRealtime } from './realtime';

window.Alpine = Alpine;

Alpine.start();

startRealtime().catch((error) => console.warn('Live updates unavailable:', error));
