<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Task Optimizer</title>

    <link rel="stylesheet" href="../CSS/style.css">

    <style>
        .eto-home {
            --eto-ink: #17233b;
            --eto-muted: #64748b;
            --eto-primary: #4f46e5;
            --eto-primary-dark: #3730a3;
            --eto-line: #e5eaf3;
            --eto-surface: #ffffff;
            --eto-bg: #f5f7fc;
            color: var(--eto-ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            line-height: 1.6;
            background: var(--eto-bg);
            padding: 34px 20px 0;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .eto-home *, .eto-home *::before, .eto-home *::after {
            box-sizing: border-box;
        }

        .eto-home a { text-decoration: none; }
        .eto-home .eto-wrap { width: min(1120px, 100%); margin: 0 auto; }

        .eto-home .eto-hero {
            position: relative;
            overflow: hidden;
            padding: clamp(34px, 6vw, 72px);
            border-radius: 28px;
            color: #fff;
            background: linear-gradient(125deg, #171d4f 0%, #4338ca 54%, #6366f1 100%);
            box-shadow: 0 24px 55px rgba(49, 46, 129, .2);
        }

        .eto-home .eto-hero::before,
        .eto-home .eto-hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            background: rgba(255,255,255,.08);
        }
        .eto-home .eto-hero::before { width: 310px; height: 310px; right: -80px; top: -130px; }
        .eto-home .eto-hero::after { width: 220px; height: 220px; right: 130px; bottom: -160px; }
        .eto-home .eto-hero-content { position: relative; z-index: 1; max-width: 720px; }
        .eto-home .eto-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 12px; margin-bottom: 18px;
            border: 1px solid rgba(255,255,255,.25); border-radius: 999px;
            background: rgba(255,255,255,.1); color: #e0e7ff;
            font-size: 12px; font-weight: 800; letter-spacing: 1.4px;
        }
        .eto-home .eto-hero h1 {
            margin: 0 0 16px; max-width: 700px; color: #fff;
            font-size: clamp(34px, 5vw, 58px); line-height: 1.08; letter-spacing: -1.5px;
        }
        .eto-home .eto-hero p {
            max-width: 620px; margin: 0; color: #e0e7ff;
            font-size: clamp(16px, 2vw, 19px); line-height: 1.8;
        }
        .eto-home .eto-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .eto-home .eto-btn {
            display: inline-flex; justify-content: center; align-items: center; gap: 10px;
            min-height: 48px; padding: 13px 22px; border: 1px solid transparent;
            border-radius: 12px; font: inherit; font-size: 15px; font-weight: 800;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .eto-home .eto-btn:hover { transform: translateY(-2px); }
        .eto-home .eto-btn-primary { background: #fff; color: var(--eto-primary-dark); box-shadow: 0 8px 20px rgba(15,23,42,.15); }
        .eto-home .eto-btn-primary:hover { background: #eef2ff; }
        .eto-home .eto-btn-secondary { border-color: rgba(255,255,255,.35); color: #fff; background: rgba(255,255,255,.08); }
        .eto-home .eto-btn-secondary:hover { background: rgba(255,255,255,.16); }

        .eto-home .eto-stats {
            display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;
            margin: 22px 0 46px;
        }
        .eto-home .eto-stat {
            padding: 22px; background: var(--eto-surface); border: 1px solid var(--eto-line);
            border-radius: 18px; box-shadow: 0 8px 24px rgba(15,23,42,.035);
        }
        .eto-home .eto-stat-label { color: var(--eto-muted); font-size: 13px; font-weight: 700; }
        .eto-home .eto-stat-value { display: block; margin: 4px 0; color: var(--eto-ink); font-size: 29px; font-weight: 850; line-height: 1.3; }
        .eto-home .eto-stat-note { color: #94a3b8; font-size: 12px; }

        .eto-home .eto-section-heading { margin: 0 0 20px; }
        .eto-home .eto-section-kicker { color: var(--eto-primary); font-size: 11px; font-weight: 850; letter-spacing: 1.8px; }
        .eto-home .eto-section-heading h2 { margin: 5px 0 6px; color: var(--eto-ink); font-size: clamp(24px, 3vw, 32px); line-height: 1.25; }
        .eto-home .eto-section-heading p { margin: 0; color: var(--eto-muted); }
        .eto-home .eto-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px; padding-bottom: 42px; }
        .eto-home .eto-panel {
            padding: clamp(22px, 3vw, 30px); border: 1px solid var(--eto-line);
            border-radius: 22px; background: var(--eto-surface); box-shadow: 0 10px 30px rgba(15,23,42,.04);
        }
        .eto-home .eto-item {
            display: flex; align-items: flex-start; gap: 15px;
            padding: 17px 0; border-top: 1px solid #edf0f6;
        }
        .eto-home .eto-item:first-of-type { margin-top: 8px; }
        .eto-home .eto-icon {
            display: grid; flex: 0 0 46px; width: 46px; height: 46px; place-items: center;
            border-radius: 14px; background: #eef2ff; color: var(--eto-primary);
            font-size: 13px; font-weight: 900;
        }
        .eto-home .eto-item h3 { margin: 0 0 4px; color: var(--eto-ink); font-size: 16px; }
        .eto-home .eto-item p { margin: 0; color: var(--eto-muted); font-size: 14px; line-height: 1.7; }
        .eto-home .eto-cta {
            display: flex; justify-content: space-between; align-items: center; gap: 22px;
            margin: 0 0 34px; padding: 25px 28px; border: 1px solid #dfe3ff;
            border-radius: 20px; background: linear-gradient(110deg, #eef2ff, #fafaff);
        }
        .eto-home .eto-cta h2 { margin: 0 0 5px; color: var(--eto-ink); font-size: 21px; }
        .eto-home .eto-cta p { margin: 0; color: var(--eto-muted); font-size: 14px; }
        .eto-home .eto-btn-cta { flex-shrink: 0; background: var(--eto-primary); color: #fff; box-shadow: 0 8px 18px rgba(79,70,229,.2); }
        .eto-home .eto-btn-cta:hover { background: var(--eto-primary-dark); }
        .eto-home .eto-footer { padding: 23px 10px; border-top: 1px solid var(--eto-line); color: #94a3b8; text-align: center; font-size: 13px; }

        @media (max-width: 850px) {
            .eto-home .eto-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .eto-home .eto-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .eto-home { padding: 18px 12px 0; }
            .eto-home .eto-hero { border-radius: 20px; }
            .eto-home .eto-stats { gap: 10px; margin-bottom: 32px; }
            .eto-home .eto-stat { padding: 16px; }
            .eto-home .eto-stat-value { font-size: 25px; }
            .eto-home .eto-cta { align-items: stretch; flex-direction: column; padding: 22px; }
            .eto-home .eto-btn { width: 100%; }
        }
    </style>
</head>
<body>
<?php require_once "../includes/navbar.php"; ?>

<main class="eto-home">
    <div class="eto-wrap">
        <section class="eto-hero">
            <div class="eto-hero-content">
                <span class="eto-eyebrow">SMART WORKFORCE PLANNING</span>
                <h1>Employee Task Optimizer</h1>
                <p>Optimize employee assignments with intelligent algorithms. Compare solutions, balance workloads, and make better use of your team's time.</p>
                <div class="eto-actions">
                    <a class="eto-btn eto-btn-primary" href="../algorithms/algorithms.php">Get Started <span aria-hidden="true">→</span></a>
                    <a class="eto-btn eto-btn-secondary" href="#eto-models">Explore Features</a>
                </div>
            </div>
        </section>

        <section class="eto-stats" aria-label="System overview">
            <div class="eto-stat"><span class="eto-stat-label">Employees</span><strong class="eto-stat-value">50</strong><span class="eto-stat-note">Total employees</span></div>
            <div class="eto-stat"><span class="eto-stat-label">Tasks</span><strong class="eto-stat-value">25</strong><span class="eto-stat-note">Available tasks</span></div>
            <div class="eto-stat"><span class="eto-stat-label">Algorithms</span><strong class="eto-stat-value">3</strong><span class="eto-stat-note">Optimization methods</span></div>
            <div class="eto-stat"><span class="eto-stat-label">System Status</span><strong class="eto-stat-value">Ready</strong><span class="eto-stat-note">Ready to optimize</span></div>
        </section>

        <section id="eto-models" class="eto-section-heading">
            <span class="eto-section-kicker">WHAT YOU CAN DO</span>
            <h2>Built for better decisions</h2>
            <p>Explore the optimization models and algorithms available in the system.</p>
        </section>

        <section class="eto-grid">
            <div class="eto-panel">
                <div class="eto-section-heading">
                    <span class="eto-section-kicker">OPTIMIZATION</span>
                    <h2>Optimization Models</h2>
                    <p>Define assignment goals and improve resource allocation.</p>
                </div>
                <div class="eto-item"><div class="eto-icon">M1</div><div><h3>Assignment Model</h3><p>Assign employees to tasks while minimizing the total assignment cost.</p></div></div>
                <div class="eto-item"><div class="eto-icon">M2</div><div><h3>Workload Model</h3><p>Balance workloads fairly across available employees.</p></div></div>
                <div class="eto-item"><div class="eto-icon">M3</div><div><h3>Cost Model</h3><p>Reduce the overall operational cost of task allocation.</p></div></div>
            </div>

            <div class="eto-panel">
                <div class="eto-section-heading">
                    <span class="eto-section-kicker">ALGORITHMS</span>
                    <h2>Available Algorithms</h2>
                    <p>Choose an approach to explore possible solutions.</p>
                </div>
                <div class="eto-item"><div class="eto-icon">GA</div><div><h3>Genetic Algorithm</h3><p>An evolutionary approach that improves solutions over generations.</p></div></div>
                <div class="eto-item"><div class="eto-icon">PSO</div><div><h3>Particle Swarm Optimization</h3><p>A swarm-intelligence method for searching promising solutions.</p></div></div>
                <div class="eto-item"><div class="eto-icon">SA</div><div><h3>Simulated Annealing</h3><p>A probabilistic technique for exploring and refining solutions.</p></div></div>
            </div>
        </section>

        <section class="eto-cta">
            <div><h2>Ready to optimize your assignments?</h2><p>Open the algorithms page to get started.</p></div>
            <a class="eto-btn eto-btn-cta" href="../algorithms/algorithms.php">Get Started <span aria-hidden="true">→</span></a>
        </section>

        <footer class="eto-footer">Employee Task Optimizer · Smarter assignments, better outcomes.</footer>
    </div>
</main>
</body>
</html>
