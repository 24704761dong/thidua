import { NavigateFunction } from 'react-router-dom';
import { TAB_PATHS } from '@/constants/paths';

type TabKey = 'home' | 'profile' | 'tasks' | 'notification' | 'setting';

const TAB_ORDER: Record<string, number> = { home: 0, profile: 1, tasks: 2, notification: 3, setting: 4 };

// Module-level direction store — allows navigate(-1) to carry direction without state
let _direction: 'forward' | 'backward' | 'fade-through' = 'forward';
export const getNavigationDirection = (): 'forward' | 'backward' | 'fade-through' => _direction;
export const setNavigationDirection = (dir: 'forward' | 'backward' | 'fade-through') => { _direction = dir; };

export function navigateForward(
  navigate: NavigateFunction,
  path: string,
  state?: Record<string, unknown>
): void {
  _direction = 'forward';
  navigate(path, state ? { state } : undefined);
}

export function navigateBack(navigate: NavigateFunction): void {
  _direction = 'backward';
  navigate(-1);
}

export function navigateTab(
  navigate: NavigateFunction,
  currentTab: string,
  targetTab: string
): void {
  if (currentTab === targetTab) return;

  const path = TAB_PATHS[targetTab];
  if (!path) return;

  _direction = 'fade-through';

  navigate(path, { replace: true });
}
