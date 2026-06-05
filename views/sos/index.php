<!-- SOS Banner -->
<div class="sos-banner" style="margin-bottom:1.5rem;animation:fade-up 0.3s var(--ease) both">
    <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">
        <div style="flex:1;min-width:200px">
            <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--red-text);margin-bottom:0.5rem">⚡ SOS Active</p>
            <h1 style="font-size:clamp(1.6rem,4vw,2.4rem);color:var(--text-primary)">Do the next five minutes.</h1>
            <p style="margin-top:0.65rem;color:#ffb3b3;font-size:0.95rem;line-height:1.65">
                Stand up. Change rooms. Put the device face-down. Breathe slowly. You are not trapped.
            </p>
        </div>
    </div>
</div>

<!-- Scripture + Safety plan -->
<div style="display:grid;gap:1.25rem;margin-bottom:1.25rem" class="sos-two-col">
    <div class="card" style="animation:fade-up 0.35s var(--ease) 0.1s both">
        <p class="text-label" style="margin-bottom:0.5rem">Scripture</p>
        <h2 style="font-size:1.1rem;color:var(--green-text);margin-bottom:0.75rem"><?= e($scripture['reference'] ?? 'Scripture') ?></h2>
        <p style="font-size:1.05rem;line-height:1.75;color:var(--text-primary)"><?= e($scripture['text'] ?? '') ?></p>
    </div>
    <div class="card" style="animation:fade-up 0.35s var(--ease) 0.15s both">
        <p class="text-label" style="margin-bottom:0.5rem">Your rescue plan</p>
        <h2 style="font-size:1.1rem;margin-bottom:0.85rem">What you planned for this moment</h2>
        <?php if ($safetyPlan): ?>
            <div class="space-y-3">
                <div>
                    <p class="form-label" style="margin-bottom:0.25rem">Your trigger</p>
                    <p style="font-size:0.9rem;color:var(--text-secondary)"><?= e($safetyPlan['top_trigger']) ?></p>
                </div>
                <div>
                    <p class="form-label" style="margin-bottom:0.25rem">Immediate action</p>
                    <p style="font-size:0.9rem;color:var(--text-secondary)"><?= e($safetyPlan['escape_action']) ?></p>
                </div>
            </div>
        <?php else: ?>
            <p class="text-sm text-muted">No safety plan yet. For now: leave the room, text someone safe, drink water.</p>
            <a href="<?= e(base_url('/purpose')) ?>" class="btn btn-ghost btn-sm" style="margin-top:0.85rem;display:inline-flex">Create one after this</a>
        <?php endif; ?>
    </div>
</div>

<!-- Log trigger -->
<form class="card" style="margin-bottom:1.25rem;animation:fade-up 0.35s var(--ease) 0.2s both" method="post" action="<?= e(base_url('/sos/trigger')) ?>">
    <?= csrf_field() ?>
    <h2 style="font-size:1.05rem;margin-bottom:0.75rem">Name the pressure — without feeding it</h2>
    <textarea class="form-textarea" name="note" placeholder="Example: alone at 11pm, tired, angry at something unrelated…" style="min-height:80px"></textarea>
    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:1rem">
        <button class="btn btn-danger" type="submit">Log SOS trigger</button>
        <button class="btn btn-primary" formaction="<?= e(base_url('/sos/resolve')) ?>" type="submit">I'm okay now ✓</button>
    </div>
</form>

<!-- Calm kit -->
<div class="card" style="margin-bottom:1.25rem;animation:fade-up 0.35s var(--ease) 0.25s both">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1.25rem">
        <div>
            <p class="text-label" style="margin-bottom:0.4rem">Calm kit</p>
            <h2 style="font-size:1.1rem">Give the urge something else to follow</h2>
            <p class="text-sm text-muted" style="margin-top:0.3rem;max-width:480px">Pick one. You're creating distance, not winning an argument in your head.</p>
        </div>
        <a class="btn btn-ghost btn-sm" href="#bible-games">Open Bible games</a>
    </div>

    <p class="form-label" style="margin-bottom:0.6rem">Soft music & worship</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.75rem">
        <?php foreach ($music as $item): ?>
            <a class="card card--surface card--link" href="<?= e($item['url']) ?>" target="_blank" rel="noopener noreferrer" style="padding:0.85rem;display:block;text-decoration:none">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem">
                    <p style="font-weight:600;font-size:0.875rem"><?= e($item['title']) ?></p>
                    <span class="badge badge-muted" style="flex-shrink:0"><?= e($item['duration_label']) ?></span>
                </div>
                <p class="text-sm text-muted" style="margin-top:0.35rem"><?= e($item['description']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($videos): ?>
        <p class="form-label" style="margin:1.25rem 0 0.6rem">Calming videos</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0.75rem">
            <?php foreach ($videos as $item): ?>
                <a class="card card--surface card--link" href="<?= e($item['url']) ?>" target="_blank" rel="noopener noreferrer" style="padding:0.85rem;display:block;text-decoration:none">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem">
                        <p style="font-weight:600;font-size:0.875rem"><?= e($item['title']) ?></p>
                        <span class="badge badge-sky" style="flex-shrink:0"><?= e($item['duration_label']) ?></span>
                    </div>
                    <p class="text-sm text-muted" style="margin-top:0.35rem"><?= e($item['description']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bible games -->
<div id="bible-games" style="display:grid;gap:1.25rem;animation:fade-up 0.35s var(--ease) 0.3s both;margin-bottom:1.25rem" class="sos-two-col">
    <div class="card">
        <h2 style="font-size:1.1rem;margin-bottom:0.85rem">Bible games</h2>
        <div class="space-y-2">
            <?php foreach ($games as $game): ?>
                <button class="sos-game" type="button"
                        data-title="<?= e($game['title']) ?>"
                        data-description="<?= e($game['description']) ?>"
                        style="width:100%;text-align:left;background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius);padding:0.75rem 0.9rem;cursor:pointer;transition:background 0.15s,border-color 0.15s"
                        onmouseover="this.style.background='var(--bg-elevated)'" onmouseout="this.style.background='var(--bg-surface)'">
                    <span style="display:block;font-weight:600;font-size:0.875rem"><?= e($game['title']) ?></span>
                    <span style="display:block;font-size:0.8rem;color:var(--text-muted);margin-top:0.2rem"><?= e($game['description']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h2 id="game-title" style="font-size:1.1rem;margin-bottom:0.4rem">Verse scramble</h2>
        <p id="game-description" class="text-sm text-muted" style="margin-bottom:1rem">Put this verse back in order. Slow hands, slow breath.</p>
        <div id="verse-words" style="display:flex;flex-wrap:wrap;gap:0.5rem;min-height:36px"></div>
        <div id="answer-words" style="min-height:60px;margin-top:0.85rem;padding:0.75rem;background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius);font-size:0.875rem;color:var(--text-secondary)"></div>
        <div style="display:flex;gap:0.65rem;margin-top:0.85rem;flex-wrap:wrap">
            <button id="reset-game" class="btn btn-ghost btn-sm" type="button">Reset</button>
            <button id="show-game-answer" class="btn btn-primary btn-sm" type="button">Show answer</button>
        </div>
        <div id="game-answer" class="hidden alert alert-success" style="margin-top:0.75rem">
            God is faithful and will provide the way of escape.
        </div>
    </div>
</div>

<!-- Calm resources -->
<div class="card" style="animation:fade-up 0.35s var(--ease) 0.35s both">
    <h2 style="font-size:1.1rem;margin-bottom:0.85rem">Quick calming resources</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0.75rem">
        <?php foreach ($calmResources as $item): ?>
            <div class="card card--surface" style="padding:0.85rem">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem">
                    <p style="font-weight:600;font-size:0.875rem"><?= e($item['title']) ?></p>
                    <span class="text-xs text-muted" style="flex-shrink:0"><?= e($item['duration_label']) ?></span>
                </div>
                <p class="text-sm text-muted" style="margin-top:0.35rem"><?= e($item['description']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
@media (min-width: 700px) {
    .sos-two-col { grid-template-columns: 1fr 1fr !important; }
}
</style>

<script>
const verse = ['God', 'is', 'faithful', 'and', 'will', 'provide', 'the', 'way', 'of', 'escape'];
let answer = [];

function shuffle(words) { return [...words].sort(() => Math.random() - 0.5); }

function renderGame() {
    answer = [];
    document.getElementById('game-answer').classList.add('hidden');
    document.getElementById('answer-words').textContent = '';
    const source = document.getElementById('verse-words');
    source.innerHTML = '';
    shuffle(verse).forEach((word) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.style.cssText = 'background:var(--bg-elevated);border:1px solid var(--border);border-radius:6px;padding:0.3rem 0.65rem;font-size:0.8rem;cursor:pointer;transition:opacity 0.15s,background 0.15s;color:var(--text-primary)';
        btn.textContent = word;
        btn.onmouseover = () => { if (!btn.disabled) btn.style.background = 'var(--bg-surface)'; };
        btn.onmouseout  = () => { if (!btn.disabled) btn.style.background = 'var(--bg-elevated)'; };
        btn.addEventListener('click', () => {
            answer.push(word);
            btn.disabled = true;
            btn.style.opacity = '0.35';
            document.getElementById('answer-words').textContent = answer.join(' ');
        });
        source.appendChild(btn);
    });
}

document.querySelectorAll('.sos-game').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('game-title').textContent = button.dataset.title;
        document.getElementById('game-description').textContent = button.dataset.description;
        renderGame();
    });
});

document.getElementById('reset-game').addEventListener('click', renderGame);
document.getElementById('show-game-answer').addEventListener('click', () => {
    document.getElementById('game-answer').classList.remove('hidden');
});
renderGame();
</script>
