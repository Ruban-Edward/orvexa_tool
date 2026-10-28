<?php
$profile = $data['profile'];
$fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
$errors = session()->getFlashdata('password_errors') ?? [];
$detailErrors = session()->getFlashdata('details_errors') ?? [];
// After a failed save, show what the user typed instead of the stored values
$value = static fn (string $field, ?string $stored) => $detailErrors ? (string) old($field, $stored) : (string) $stored;
$dob = $profile['date_of_birth'] ?? null;
?>
<div class="ox-profile">
    <section class="ox-panel ox-profile-card">
        <span class="ox-profile-avatar" aria-hidden="true"><?= esc(strtoupper(mb_substr($profile['first_name'] ?? '?', 0, 1))) ?></span>
        <div class="ox-profile-id">
            <h2><?= esc($fullName) ?></h2>
            <span class="ox-tag ox-tag-green"><?= esc(ucwords((string) ($profile['role_name'] ?? 'Member'))) ?></span>
        </div>
        <dl class="ox-profile-facts">
            <div><dt><i class="icon-user" aria-hidden="true"></i>Username</dt><dd><?= esc($profile['external_username'] ?? '—') ?></dd></div>
            <div><dt><i class="icon-mail" aria-hidden="true"></i>Email</dt><dd><?= esc($profile['email_id'] ?? '—') ?></dd></div>
            <div><dt><i class="icon-hash" aria-hidden="true"></i>Employee ID</dt><dd><?= esc($profile['external_employee_id'] ?? '—') ?></dd></div>
            <div><dt><i class="icon-gift" aria-hidden="true"></i>Date of birth</dt><dd><?= $dob ? esc(date('d M Y', strtotime($dob))) : '<a href="#details">Add</a>' ?></dd></div>
            <div><dt><i class="icon-calendar" aria-hidden="true"></i>Member since</dt><dd><?= ! empty($profile['created_date']) ? esc(date('d M Y', strtotime($profile['created_date']))) : '—' ?></dd></div>
        </dl>
        <a class="ox-profile-work" href="<?= ASSERT_PATH ?>mywork">
            <span class="ox-stat-icon ox-tone-green"><i class="icon-check-square" aria-hidden="true"></i></span>
            <span><strong><?= (int) $data['openTasks'] ?></strong> open <?= $data['openTasks'] === 1 ? 'task' : 'tasks' ?> assigned to you</span>
            <i class="icon-chevron-right" aria-hidden="true"></i>
        </a>
    </section>

    <div class="ox-profile-side">
        <section class="ox-panel" id="details">
            <header class="ox-panel-head">
                <h2 class="ox-panel-title"><i class="icon-user" aria-hidden="true"></i> Personal details</h2>
            </header>
            <form class="ox-profile-form ox-details-form" method="post" action="<?= ASSERT_PATH ?>profile/details" id="detailsForm" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="first_name">First name</label>
                        <input type="text" class="form-control <?= isset($detailErrors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name"
                               value="<?= esc($value('first_name', $profile['first_name'] ?? ''), 'attr') ?>" maxlength="50" required autocomplete="given-name"
                               <?= isset($detailErrors['first_name']) ? 'aria-describedby="first_name_error"' : '' ?>>
                        <div class="invalid-feedback" id="first_name_error"><?= esc($detailErrors['first_name'] ?? 'Enter your first name.') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="last_name">Last name</label>
                        <input type="text" class="form-control <?= isset($detailErrors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name"
                               value="<?= esc($value('last_name', $profile['last_name'] ?? ''), 'attr') ?>" maxlength="50" autocomplete="family-name">
                        <div class="invalid-feedback"><?= esc($detailErrors['last_name'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="profileUsername">Username</label>
                        <input type="text" class="form-control <?= isset($detailErrors['username']) ? 'is-invalid' : '' ?>" id="profileUsername" name="username"
                               value="<?= esc($value('username', $profile['external_username'] ?? ''), 'attr') ?>" minlength="3" maxlength="50" required
                               pattern="[A-Za-z0-9._\-]+" autocomplete="username" spellcheck="false"
                               data-current="<?= esc($profile['external_username'] ?? '', 'attr') ?>" aria-describedby="usernameStatus">
                        <div class="form-text ox-username-status" id="usernameStatus" aria-live="polite"><?= isset($detailErrors['username']) ? '' : 'You sign in with this username.' ?></div>
                        <div class="invalid-feedback d-block" id="username_error"><?= esc($detailErrors['username'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="date_of_birth">Date of birth <span class="ox-optional">(optional)</span></label>
                        <input type="date" class="form-control <?= isset($detailErrors['date_of_birth']) ? 'is-invalid' : '' ?>" id="date_of_birth" name="date_of_birth"
                               value="<?= esc($value('date_of_birth', $dob), 'attr') ?>" min="1900-01-01" max="<?= date('Y-m-d') ?>" autocomplete="bday">
                        <div class="invalid-feedback"><?= esc($detailErrors['date_of_birth'] ?? 'Enter a date between 1900 and today.') ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mt-3">
                    <button type="submit" class="btn btn-primary" id="saveDetailsBtn">Save details</button>
                    <span class="form-text m-0">Your email (<?= esc($profile['email_id'] ?? '') ?>) and role are managed by an administrator.</span>
                </div>
            </form>
        </section>

        <section class="ox-panel">
            <header class="ox-panel-head">
                <h2 class="ox-panel-title">My products <span class="ox-badge-count"><?= count($data['products']) ?></span></h2>
            </header>
            <?php if ($data['products'] === []): ?>
                <div class="ox-empty">
                    <i class="icon-package" aria-hidden="true"></i>
                    <h4>No products yet</h4>
                    <p>Ask a product owner or admin to add you to a product.</p>
                </div>
            <?php else: ?>
                <ul class="ox-profile-products">
                    <?php foreach ($data['products'] as $product): ?>
                        <li>
                            <a href="<?= ASSERT_PATH ?>backlog/backlogitems?pid=<?= (int) $product['product_id'] ?>">
                                <span class="ox-avatar-square"><?= esc(strtoupper(mb_substr(trim($product['product_name']), 0, 1))) ?></span>
                                <span class="ox-profile-product-name"><?= esc($product['product_name']) ?></span>
                                <?php if (! empty($data['productRoles'][$product['product_id']])): ?>
                                    <span class="ox-tag ox-tag-green"><?= esc($data['productRoles'][$product['product_id']]) ?></span>
                                <?php endif; ?>
                                <span class="ox-profile-product-meta">
                                    <?= (int) $product['number_of_backlog_items'] ?> backlog items · <?= (int) $product['number_of_user_stories'] ?> stories
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="ox-panel" id="password">
            <header class="ox-panel-head">
                <h2 class="ox-panel-title"><i class="icon-lock" aria-hidden="true"></i> Change password</h2>
            </header>
            <?php if (session('must_change_password')): ?>
                <div class="alert alert-warning m-3 mb-0" role="alert">
                    You signed in with a temporary password. Choose your own password to start using Orvexa.
                </div>
            <?php endif; ?>
            <form class="ox-profile-form" method="post" action="<?= ASSERT_PATH ?>profile/password" autocomplete="off" novalidate>
                <?php foreach ([
                    'current_password' => ['Current password', 'current-password'],
                    'new_password'     => ['New password', 'new-password'],
                    'confirm_password' => ['Confirm new password', 'new-password'],
                ] as $field => [$label, $autocomplete]): ?>
                    <div class="mb-3">
                        <label class="form-label" for="<?= $field ?>"><?= $label ?></label>
                        <input type="password" class="form-control <?= isset($errors[$field]) ? 'is-invalid' : '' ?>" id="<?= $field ?>" name="<?= $field ?>"
                               autocomplete="<?= $autocomplete ?>" required <?= $field !== 'current_password' ? 'minlength="8" maxlength="72"' : '' ?>
                               <?= isset($errors[$field]) ? 'aria-describedby="' . $field . '_error"' : '' ?>>
                        <?php if (isset($errors[$field])): ?>
                            <div class="invalid-feedback" id="<?= $field ?>_error"><?= esc($errors[$field]) ?></div>
                        <?php elseif ($field === 'new_password'): ?>
                            <div class="form-text">At least 8 characters.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary">Update password</button>
            </form>
        </section>
    </div>
</div>
