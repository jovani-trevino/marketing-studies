import { spawnSync } from 'node:child_process';
import { readdirSync } from 'node:fs';
import { join, relative } from 'node:path';
import { root, runPhp } from './php-runner.mjs';

const skip = new Set(['.git', 'node_modules', '.preview', '.runtime', '.outputs', '.sites-runtime', 'coverage']);
const files = [];
function visit(directory) {
  for (const entry of readdirSync(directory, { withFileTypes: true })) {
    const path = join(directory, entry.name);
    const name = relative(root, path).replaceAll('\\', '/');
    if (skip.has(entry.name) || ['blog/storage', 'blog/public/uploads', 'blog/public/static/vendor'].includes(name)) continue;
    if (entry.isDirectory()) visit(path);
    else if (entry.isFile() && /\.(php|m?js)$/.test(entry.name) && !/config\.local\.php$/.test(entry.name)) files.push(name);
  }
}

try {
  visit(root);
  const php = files.filter(file => file.endsWith('.php'));
  // PHP before 8.3 checks only the first filename passed to -l.
  for (const file of php) {
    const phpCheck = runPhp(['-l', file]);
    if (phpCheck.status !== 0) throw new Error(phpCheck.stdout + phpCheck.stderr);
  }
  const javascript = files.filter(file => /\.m?js$/.test(file));
  for (const file of javascript) {
    const checked = spawnSync(process.execPath, ['--check', file], { cwd: root, encoding: 'utf8' });
    if (checked.error || checked.status !== 0) throw new Error(`${file}\n${checked.error?.message || checked.stdout + checked.stderr}`);
  }
  console.log(`Sintaxis correcta: ${php.length} archivos PHP y ${javascript.length} archivos JavaScript.`);
} catch (error) {
  console.error(error.message);
  process.exitCode = 1;
}
