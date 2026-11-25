import { mkdtempSync, rmSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { root, runPhp } from './php-runner.mjs';

const fixture = mkdtempSync(join(tmpdir(), 'marketing-studies-import-test-'));
let failures = 0;
try {
  const backend = runPhp(['tests/backend_test.php'], { stdio: 'inherit' });
  if (backend.status !== 0) failures++;
  const routes = spawnSync(process.execPath, ['tests/routes_test.mjs'], { cwd: root, stdio: 'inherit' });
  if (routes.error || routes.status !== 0) failures++;
  const consent = spawnSync(process.execPath, ['--test', 'tests/consent_test.mjs'], { cwd: root, stdio: 'inherit' });
  if (consent.error || consent.status !== 0) failures++;
  for (const scenario of ['dry', 'empty', 'repeat', 'existing', 'preserve', 'rollback']) {
    const result = runPhp(['tests/importer_test.php', scenario, fixture], { stdio: 'inherit' });
    if (result.status !== 0) failures++;
  }
} catch (error) {
  console.error(error.message);
  failures++;
} finally {
  rmSync(fixture, { recursive: true, force: true });
}
if (failures) process.exitCode = 1;
else console.log('Pruebas completas. Ninguna conexión a bases de datos, cuentas o correo de producción.');
