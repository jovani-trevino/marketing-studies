import { spawnSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

export const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
export const phpBinary = process.env.MS_PHP_BINARY || (existsSync(resolve(root, '.runtime/php')) ? resolve(root, '.runtime/php') : 'php');

export function runPhp(args, options = {}) {
  const result = spawnSync(phpBinary, args, { cwd: root, encoding: 'utf8', maxBuffer: 8 * 1024 * 1024, ...options });
  if (result.error) throw new Error(`No se pudo ejecutar PHP. Instala PHP 8.3+ o configura MS_PHP_BINARY con una ruta al ejecutable. ${result.error.message}`);
  return result;
}
