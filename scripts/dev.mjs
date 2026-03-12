import { spawn } from 'node:child_process';

const isWin = process.platform === 'win32';
const npmCmd = isWin ? 'npm.cmd' : 'npm';

const backend = spawn(npmCmd, ['run', 'dev', '-w', '@ra-jiraf/backend'], {
  stdio: 'inherit',
  shell: false
});

const frontend = spawn(npmCmd, ['run', 'dev', '-w', '@ra-jiraf/frontend'], {
  stdio: 'inherit',
  shell: false
});

const shutdown = (code = 0) => {
  backend.kill('SIGTERM');
  frontend.kill('SIGTERM');
  process.exit(code);
};

backend.on('exit', (code) => {
  if (code && code !== 0) shutdown(code);
});

frontend.on('exit', (code) => {
  if (code && code !== 0) shutdown(code);
});

process.on('SIGINT', () => shutdown(0));
process.on('SIGTERM', () => shutdown(0));
