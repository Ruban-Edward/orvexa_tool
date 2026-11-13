<?php /* Decorative right-hand panel shared by the sign-in and password reset pages. */ ?>
        <aside class="ox-auth-stage" aria-hidden="true">
            <div class="ox-stage-copy">
                <p class="ox-stage-eyebrow">Orvexa for Scrum teams</p>
                <h2>Orchestrate work.<br><span>Accelerate execution.</span></h2>
                <p>Backlog, sprints, ceremonies and reports — one calm place for the whole team.</p>
            </div>

            <div class="ox-stage-preview">
                <div class="ox-mock ox-mock-sprint">
                    <div class="ox-mock-head">
                        <span class="ox-mock-tag">Sprint 12</span>
                        <span class="ox-mock-live">In progress</span>
                    </div>
                    <div class="ox-mock-sprint-body">
                        <div class="ox-ring" style="--p: 68">
                            <strong>68%</strong>
                            <small>done</small>
                        </div>
                        <dl>
                            <div><dt>Tasks</dt><dd>24 / 35</dd></div>
                            <div><dt>Days left</dt><dd>6</dd></div>
                            <div><dt>Velocity</dt><dd>42 pts</dd></div>
                        </dl>
                    </div>
                    <div class="ox-mock-roles">
                        <span>PO</span><span>SM</span><span>DEV</span><span>DEV</span>
                    </div>
                </div>

                <div class="ox-mock ox-mock-burn">
                    <div class="ox-mock-head">
                        <span class="ox-mock-title">Burndown</span>
                        <span class="ox-mock-delta">On track</span>
                    </div>
                    <svg viewBox="0 0 220 90" preserveAspectRatio="none">
                        <path class="ox-burn-grid" d="M0 22.5h220M0 45h220M0 67.5h220"/>
                        <path class="ox-burn-ideal" d="M0 8 L220 86"/>
                        <path class="ox-burn-area" d="M0 8 L30 14 L60 26 L90 30 L120 44 L150 52 L150 90 L0 90Z"/>
                        <path class="ox-burn-line" d="M0 8 L30 14 L60 26 L90 30 L120 44 L150 52"/>
                        <circle class="ox-burn-dot" cx="150" cy="52" r="4"/>
                    </svg>
                </div>

                <div class="ox-mock ox-mock-toast">
                    <span class="ox-toast-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    <div>
                        <strong>Daily scrum</strong>
                        <small>Starts in 10 min · 15 min sync</small>
                    </div>
                </div>
            </div>

            <ul class="ox-stage-flow">
                <li>Backlog</li>
                <li>Planning</li>
                <li>Sprint</li>
                <li>Review</li>
                <li>Retro</li>
            </ul>
        </aside>
