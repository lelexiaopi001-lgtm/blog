<?php /* 访问统计 */ $range = [7 => '近 7 天', 30 => '近 30 天', 90 => '近 90 天']; ?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-head"><?= adminIcon('i-eye') ?><span class="stat-label">总浏览量 PV</span></div>
        <div class="stat-value"><?= number_format($pvTotal) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-head"><?= adminIcon('i-profile') ?><span class="stat-label">总访客数 UV</span></div>
        <div class="stat-value"><?= number_format($uvTotal) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-head"><?= adminIcon('i-stats') ?><span class="stat-label">日均 PV</span></div>
        <div class="stat-value"><?= number_format($avgPv) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-head"><?= adminIcon('i-book-open') ?><span class="stat-label">PV/UV 比</span></div>
        <div class="stat-value"><?= $pvTotal ? round($pvTotal / max(1, $uvTotal), 1) : 0 ?></div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3><?= adminIcon('i-stats') ?> 访问趋势</h3>
        <div class="tabs">
            <?php foreach ($range as $k => $label): ?>
            <a class="tab <?= $days === $k ? 'active' : '' ?>" href="/admin/index.php?r=stats&days=<?= $k ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="chart-box chart-lg" id="statsChart"
         data-labels='<?= Helper::e(json_encode($labels, JSON_UNESCAPED_UNICODE)) ?>'
         data-pv='<?= Helper::e(json_encode($pv)) ?>' data-uv='<?= Helper::e(json_encode($uv)) ?>'></div>
</div>

<div class="two-col">
    <div class="panel">
        <div class="panel-head"><h3>热门文章 Top 10</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>#</th><th>标题</th><th>浏览</th><th>点赞</th><th>评论</th></tr></thead>
                <tbody>
                <?php if (empty($hot)): ?><tr><td colspan="5" class="table-empty">暂无数据</td></tr><?php endif; ?>
                <?php foreach ($hot as $i => $h): ?>
                <tr>
                    <td><span class="hot-rank r<?= $i + 1 ?>"><?= $i + 1 ?></span></td>
                    <td><a class="table-title" href="/post/<?= Helper::e($h['slug']) ?>" target="_blank"><?= Helper::e($h['title']) ?></a></td>
                    <td><?= (int) $h['views'] ?></td>
                    <td><?= (int) $h['likes'] ?></td>
                    <td><?= (int) $h['comment_count'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>分类分布</h3></div>
        <div class="bar-chart" id="catChart">
            <?php $max = max(1, max(array_column($catStat, 'post_count') ?: [1])); ?>
            <?php foreach ($catStat as $c): ?>
            <div class="bar-row">
                <span class="bar-label"><?= Helper::e($c['name']) ?></span>
                <div class="bar-track"><div class="bar-fill" style="width:<?= round($c['post_count'] / $max * 100) ?>%"></div></div>
                <em><?= (int) $c['post_count'] ?></em>
            </div>
            <?php endforeach; ?>
            <?php if (empty($catStat)): ?><p class="table-empty">暂无分类</p><?php endif; ?>
        </div>
    </div>
</div>
