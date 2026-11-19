<div class="admin-dashboard">
    <div class="page-header">
        <h1>⚙️ Backoffice Administrateur</h1>
        <p class="subtitle">Gestion centralisée des utilisateurs, du catalogue et des commandes.</p>
    </div>

    <!-- 1. Gestion des Utilisateurs -->
    <section class="admin-section">
        <h2 class="section-title">Utilisateurs enregistrés (<?= count($users) ?>)</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u->getId() ?></td>
                            <td><?= htmlspecialchars($u->getFullName(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($u->getEmail(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($u->isActive()): ?>
                                    <span class="badge badge-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-role"><?= htmlspecialchars($u->getRole(), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <form action="/admin/users/<?= $u->getId() ?>/role" method="POST" class="inline-form">
                                    <?= \App\Core\Csrf::field() ?>
                                    <select name="role" class="form-select-sm">
                                        <option value="ROLE_CLIENT" <?= $u->getRole() === 'ROLE_CLIENT' ? 'selected' : '' ?>>Client</option>
                                        <option value="ROLE_ADMIN" <?= $u->getRole() === 'ROLE_ADMIN' ? 'selected' : '' ?>>Admin</option>
                                        <option value="ROLE_INSTRUCTOR" <?= $u->getRole() === 'ROLE_INSTRUCTOR' ? 'selected' : '' ?>>Formateur</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline btn-sm">Changer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 2. Gestion des Cursus -->
    <section class="admin-section">
        <h2 class="section-title">Gestion des Formations (<?= count($courses) ?>)</h2>
        <div class="admin-split">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Thème</th>
                            <th>Prix</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c->getTitle(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($c->getThemeName() ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= number_format($c->getPrice(), 2, ',', ' ') ?> €</td>
                                <td>
                                    <form action="/admin/courses/<?= $c->getId() ?>/delete" method="POST" onsubmit="return confirm('Confirmer la suppression de ce cursus ?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Formulaire d'ajout rapide de cours -->
            <div class="admin-card-form">
                <h3>Ajouter un Cursus</h3>
                <form action="/admin/courses/create" method="POST" class="auth-form">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="form-group">
                        <label>Titre du cursus *</label>
                        <input type="text" name="title" required placeholder="Ex: Masterclass Piano Jazz">
                    </div>
                    <div class="form-group">
                        <label>Thème *</label>
                        <select name="theme_id" class="form-select">
                            <option value="1">Musique</option>
                            <option value="2">Informatique</option>
                            <option value="3">Jardinage</option>
                            <option value="4">Cuisine</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Prix (€) *</label>
                        <input type="number" step="0.01" name="price" required placeholder="45.00">
                    </div>
                    <div class="form-group">
                        <label>Description courte</label>
                        <textarea name="description" rows="3" class="form-textarea"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Créer le cursus</button>
                </form>
            </div>
        </div>
    </section>

    <!-- 3. Historique des Commandes -->
    <section class="admin-section">
        <h2 class="section-title">Commandes Récentes (<?= count($orders) ?>)</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Réf</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($ord['order_reference'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars($ord['first_name'] . ' ' . $ord['last_name'] . ' (' . $ord['user_email'] . ')', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= number_format((float)$ord['total_amount'], 2, ',', ' ') ?> €</strong></td>
                            <td>
                                <?php if ($ord['status'] === 'paid'): ?>
                                    <span class="badge badge-success">Payée</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= htmlspecialchars($ord['status'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
