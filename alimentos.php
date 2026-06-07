<?php
// alimentos.php
require_once 'includes/auth.php'; requireLogin();
require_once 'includes/db.php';
require_once 'includes/roles.php';
require_once 'models/AlimentoModel.php';

$model = new AlimentoModel($conn);
$msg = $err = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $datos=['nombre'=>trim($_POST['nombre']??''),'categoria'=>$_POST['categoria']??'otro','calorias'=>$_POST['calorias']??0,'proteinas_g'=>$_POST['proteinas_g']??0,'carbohidratos_g'=>$_POST['carbohidratos_g']??0,'grasas_g'=>$_POST['grasas_g']??0,'hierro_mg'=>$_POST['hierro_mg']??0,'calcio_mg'=>$_POST['calcio_mg']??0,'vitamina_d_ug'=>$_POST['vitamina_d_ug']??0,'zinc_mg'=>$_POST['zinc_mg']??0];
    if($datos['nombre']) $model->crear($datos)?$msg='Alimento agregado.':$err='Error al guardar.';
    else $err='El nombre es obligatorio.';
}
if(isset($_GET['del'])){$model->eliminar((int)$_GET['del']);$msg='Alimento eliminado.';}

$cat_f=$_GET['cat']??'';
$alimentos=$model->obtenerTodos($cat_f);

$page_title='Catálogo de Alimentos'; $page_sub='Valores nutricionales'; $active_menu='alimentos';
include 'includes/header.php';
?>
<?php if($msg): ?><div class="alert-box success">✅ <?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if($err): ?><div class="alert-box error">❌ <?=htmlspecialchars($err)?></div><?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <?php foreach([''=>'Todos','cereal'=>'🌾 Cereal','proteina'=>'🥩 Proteína','lacteo'=>'🥛 Lácteo','verdura'=>'🥦 Verdura','fruta'=>'🍎 Fruta','bebida'=>'🥤 Bebida','otro'=>'🍴 Otro'] as $v=>$l): ?>
        <a href="alimentos.php?cat=<?=$v?>" class="btn <?=$cat_f===$v?'btn-primary':'btn-secondary'?> btn-sm"><?=$l?></a>
        <?php endforeach; ?>
    </div>
    <?php if(tienePermiso('alimentos')): ?><button class="btn btn-primary" data-modal="modalAlimento">➕ Nuevo Alimento</button><?php endif; ?>
</div>

<div class="card">
    <div class="table-wrap"><table>
        <thead><tr><th>Alimento</th><th>Categoría</th><th>Calorías</th><th>Proteínas</th><th>Hierro</th><th>Calcio</th><th>Vit. D</th><th>Zinc</th><?php if(tienePermiso('alimentos')): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
        <?php if($alimentos&&$alimentos->num_rows>0): while($a=$alimentos->fetch_assoc()): ?>
        <tr>
            <td><strong><?=htmlspecialchars($a['nombre'])?></strong></td>
            <td><?=ucfirst($a['categoria'])?></td>
            <td><?=$a['calorias']?> kcal</td>
            <td><?=$a['proteinas_g']?> g</td>
            <td><?=$a['hierro_mg']?> mg</td>
            <td><?=$a['calcio_mg']?> mg</td>
            <td><?=$a['vitamina_d_ug']?> μg</td>
            <td><?=$a['zinc_mg']?> mg</td>
            <?php if(tienePermiso('alimentos')): ?><td><a href="alimentos.php?del=<?=$a['id']?>" class="btn btn-danger btn-sm" data-confirm="¿Eliminar?">🗑️</a></td><?php endif; ?>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="9"><div class="empty-state"><div class="icon">🥦</div><p>Sin alimentos</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>

<?php if(tienePermiso('alimentos')): ?>
<div class="modal-overlay" id="modalAlimento"><div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalAlimento').classList.remove('show')">✕</button>
    <div class="modal-title">➕ Nuevo Alimento</div>
    <form method="POST">
        <?php csrf_field(); ?>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Categoría</label>
                <select name="categoria" class="form-control"><option value="cereal">🌾 Cereal</option><option value="proteina">🥩 Proteína</option><option value="lacteo">🥛 Lácteo</option><option value="verdura">🥦 Verdura</option><option value="fruta">🍎 Fruta</option><option value="bebida">🥤 Bebida</option><option value="otro">🍴 Otro</option></select>
            </div>
        </div>
        <div class="form-row-3">
            <?php foreach(['calorias'=>'Calorías (kcal)','proteinas_g'=>'Proteínas (g)','carbohidratos_g'=>'Carbs (g)','grasas_g'=>'Grasas (g)','hierro_mg'=>'Hierro (mg)','calcio_mg'=>'Calcio (mg)','vitamina_d_ug'=>'Vit. D (μg)','zinc_mg'=>'Zinc (mg)'] as $f=>$l): ?>
            <div class="form-group"><label class="form-label"><?=$l?></label><input type="number" name="<?=$f?>" class="form-control" step="0.01" value="0"></div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalAlimento').classList.remove('show')">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div></div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
