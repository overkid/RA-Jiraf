import { spawn } from 'node:child_process';

const isWin = process.platform === 'win32';

const runScript = (script) => {
  if (isWin) {
    const comspec = process.env.ComSpec || 'cmd.exe';
    return spawn(comspec, ['/d', '/s', '/c', script], {
      stdio: 'inherit',
      windowsHide: true
    });
  }

  return spawn('sh', ['-lc', script], {
    stdio: 'inherit'
  });
};

const backend = runScript('npm run dev -w @ra-jiraf/backend');
const frontend = runScript('npm run dev -w @ra-jiraf/frontend');

let shuttingDown = false;

const shutdown = (code = 0) => {
  if (shuttingDown) return;
  shuttingDown = true;

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
