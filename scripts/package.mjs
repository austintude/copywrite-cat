import { rm, mkdir, cp } from 'node:fs/promises';
import path from 'node:path';

const root = process.cwd();
const distRoot = path.join(root, 'dist');
const outDir = path.join(distRoot, 'copywrite-cat');

const copyIfExists = async (rel) => {
	const src = path.join(root, rel);
	const dest = path.join(outDir, rel);
	await cp(src, dest, { recursive: true });
};

await rm(distRoot, { recursive: true, force: true });
await mkdir(outDir, { recursive: true });

// Copy plugin runtime files only.
await copyIfExists('copywrite-cat.php');
await copyIfExists('uninstall.php');
await copyIfExists('readme.txt');

// Runtime directories
try { await copyIfExists('build'); } catch {}
try { await copyIfExists('includes'); } catch {}
try { await copyIfExists('templates'); } catch {}

console.log('Packaged to', outDir);
