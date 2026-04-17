// Auto-sync hook: when an Edit/Write touches a PHP file under app/, database/seeders/,
// or database/factories/ in this worktree, mirror it to the parent project so PHP's
// composer autoload (which dereferences the vendor junction back to parent paths)
// picks up the change immediately without manual `cp`.
const fs = require('fs');
const path = require('path');

let input = '';
process.stdin.on('data', c => input += c);
process.stdin.on('end', () => {
    try {
        const m = JSON.parse(input);
        const file = m.tool_input?.file_path;
        if (!file) return;
        const worktree = path.normalize('C:/Users/Admin/Desktop/Pult2.0/.claude/worktrees/upbeat-pasteur-0723b7');
        const parent = path.normalize('C:/Users/Admin/Desktop/Pult2.0');
        const fileN = path.normalize(file);
        if (!fileN.startsWith(worktree + path.sep)) return;
        const rel = fileN.substring(worktree.length + 1);
        if (!/^(app[\\/]|database[\\/](seeders|factories)[\\/]|routes[\\/])/.test(rel)) return;
        const dst = path.join(parent, rel);
        fs.mkdirSync(path.dirname(dst), { recursive: true });
        fs.copyFileSync(fileN, dst);
    } catch (e) {
        // Stay silent — hooks must not break the user's workflow.
    }
});
