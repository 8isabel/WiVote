<?php

$username = $_SESSION['username'] ?? 'Guest';
$miis = [];

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 5) {
        return 'just now';
    }
    $units = [
        31536000 => 'year',
        2592000  => 'month',
        86400    => 'day',
        3600     => 'hour',
        60       => 'minute',
        1        => 'second',
    ];
    foreach ($units as $seconds => $label) {
        $count = (int) floor($diff / $seconds);
        if ($count >= 1) {
            return $count . ' ' . $label . ($count === 1 ? '' : 's') . ' ago';
        }
    }
    return 'just now';
}

function renderMiiPfp(array $item, string $key, array &$miis): void
{
    $miis[$key] = [
        'skin_color'  => $item['skin_color'] ?? '#F2C396',
        'face_shape'  => ($item['face_shape'] ?? null) !== null ? (int) $item['face_shape'] : 0,
        'hair'        => ($item['hair'] ?? null) !== null ? (int) $item['hair'] : 0,
        'hair_color'  => $item['hair_color'] ?? '#3B2314',
        'eyebrows'    => ($item['eyebrows'] ?? null) !== null ? (int) $item['eyebrows'] : 0,
        'eyes'        => ($item['eyes'] ?? null) !== null ? (int) $item['eyes'] : 0,
        'eye_color'   => $item['eye_color'] ?? '#4A3728',
        'nose'        => ($item['nose'] ?? null) !== null ? (int) $item['nose'] : 0,
        'mouth'       => ($item['mouth'] ?? null) !== null ? (int) $item['mouth'] : 0,
        'facial_hair' => ($item['facial_hair'] ?? null) !== null ? (int) $item['facial_hair'] : null,
        'glasses'     => ($item['glasses'] ?? null) !== null ? (int) $item['glasses'] : null,
        'shirt'       => ($item['shirt'] ?? null) !== null ? (int) $item['shirt'] : 0,
        'shirt_color' => $item['shirt_color'] ?? '#81B6FF',
    ];
    ?>
    <span class="mii-pfp" data-mii="<?= e($key) ?>"></span>
    <?php
}

function renderComments(string $targetType, int $targetId, array $commentsByTarget, bool $isLoggedIn, int $filterCategory): void
{
    $comments = $commentsByTarget[$targetType][$targetId] ?? [];

    $topLevel = [];
    $replies  = [];
    foreach ($comments as $c) {
        if (!empty($c['parent_id'])) {
            $replies[(int) $c['parent_id']][] = $c;
        } else {
            $topLevel[] = $c;
        }
    }
    ?>
    <div class="comments">
        <?php if ($comments): ?>
            <div class="comment-list">
                <?php foreach ($topLevel as $comment): ?>
                    <?php renderComment($comment, $replies, $isLoggedIn, $filterCategory, $targetType, $targetId, false); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-comments">No comments yet.</p>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <form class="comment-form" method="post" action="polls.php<?= $filterCategory > 0 ? '?category=' . $filterCategory : '' ?>">
                <input type="hidden" name="action" value="comment">
                <input type="hidden" name="target_type" value="<?= e($targetType) ?>">
                <input type="hidden" name="target_id" value="<?= $targetId ?>">
                <input type="text" name="content" maxlength="500" placeholder="Write a comment..." required>
                <button type="submit">Send</button>
            </form>
        <?php else: ?>
            <a class="comment-login" href="login.php">Log in to comment</a>
        <?php endif; ?>
    </div>
    <?php
}

function renderComment(array $comment, array $replies, bool $isLoggedIn, int $filterCategory, string $targetType, int $targetId, bool $isReply): void
{
    global $miis;

    $id    = (int) $comment['id'];
    $likes = (int) ($comment['like_count'] ?? 0);
    $liked = !empty($comment['liked_by_me']);
    ?>
    <div class="comment<?= $isReply ? ' reply' : '' ?>" id="comment-<?= $id ?>">
        <?php renderMiiPfp($comment, 'comment-' . $id, $miis); ?>
        <div class="comment-body">
            <div class="comment-head">
                <span class="comment-author"><?= e($comment['username']) ?>:</span>
                <span class="comment-text"><?= e($comment['content']) ?></span>
            </div>
            <div class="comment-meta">
                <span class="comment-time"><?= timeAgo($comment['created_at']) ?></span>
                <?php if ($isLoggedIn): ?>
                    <form class="like-form" method="post" action="polls.php<?= $filterCategory > 0 ? '?category=' . $filterCategory : '' ?>">
                        <input type="hidden" name="action" value="like_comment">
                        <input type="hidden" name="comment_id" value="<?= $id ?>">
                        <button type="submit" class="like-btn<?= $liked ? ' liked' : '' ?>" title="<?= $liked ? 'Unlike' : 'Like' ?>">
                            <?= $liked ? '&#9829;' : '&#9825;' ?><?= $likes > 0 ? ' ' . $likes : '' ?>
                        </button>
                    </form>
                    <?php if (!$isReply): ?>
                        <button type="button" class="reply-toggle"
                                onclick="document.getElementById('reply-form-<?= $id ?>').classList.toggle('open')">Reply</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ($isLoggedIn && !$isReply): ?>
                <form class="comment-form reply-form" id="reply-form-<?= $id ?>" method="post" action="polls.php<?= $filterCategory > 0 ? '?category=' . $filterCategory : '' ?>">
                    <input type="hidden" name="action" value="comment">
                    <input type="hidden" name="target_type" value="<?= e($targetType) ?>">
                    <input type="hidden" name="target_id" value="<?= $targetId ?>">
                    <input type="hidden" name="parent_id" value="<?= $id ?>">
                    <input type="text" name="content" maxlength="500" placeholder="Reply to <?= e($comment['username']) ?>..." required>
                    <button type="submit">Send</button>
                </form>
            <?php endif; ?>
            <?php if (!empty($replies[$id])): ?>
                <div class="reply-list">
                    <?php foreach ($replies[$id] as $reply): ?>
                        <?php renderComment($reply, $replies, $isLoggedIn, $filterCategory, $targetType, $targetId, true); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/polls.css">
    <link href='https://fonts.googleapis.com/css?family=Turret Road' rel='stylesheet'>
    <link href="https://fonts.cdnfonts.com/css/porter-sans-block" rel="stylesheet">
    <title>WiVote Feed</title>
</head>
<body>
<div class="title">
    <h1>WiVote Feed</h1>
</div>

<?php if ($flash !== ''): ?>
    <p class="flash"><?= e($flash) ?></p>
<?php endif; ?>

<?php if (!empty($topics)): ?>
    <p class="topics-label">Popular topics</p>
    <div class="topics-row">
        <?php foreach ($topics as $topic): ?>
            <?php $tVotes = (int) $topic['total_votes']; ?>
            <a class="topic-chip" href="#poll-<?= (int) $topic['id'] ?>">
                <?php if (!empty($topic['image_url'])): ?>
                    <img src="<?= e($topic['image_url']) ?>" alt="">
                <?php else: ?>
                    <span class="topic-letter"><?= e(strtoupper(mb_substr($topic['title'], 0, 1))) ?></span>
                <?php endif; ?>
                <span class="topic-chip-text">
                    <span class="topic-chip-title"><?= e($topic['title']) ?></span>
                    <span class="topic-chip-votes"><?= $tVotes ?> vote<?= $tVotes === 1 ? '' : 's' ?></span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($isLoggedIn): ?>
    <a class="new-poll-toggle" href="create.php<?= $filterCategory > 0 ? '?category=' . $filterCategory : '' ?>">+ New poll or post</a>
<?php else: ?>
    <a class="new-poll-toggle" href="login.php">+ New poll or post</a>
<?php endif; ?>

<?php if (empty($feed)): ?>
    <div class="empty-state">
        <?php if ($filterCategory > 0 && !empty($categories)): ?>
            <p class="empty-title">Nothing in this category yet!</p>
        <?php else: ?>
            <p class="empty-title">Nothing here yet!</p>
        <?php endif; ?>
        <p class="empty-text">Be the first to post a poll or a post.</p>
    </div>
<?php else: ?>
    <div class="poll-grid">
        <?php foreach ($feed as $item): ?>
            <?php if ($item['kind'] === 'poll'): ?>
                <?php
                    $poll       = $item['data'];
                    $pollId     = (int) $poll['id'];
                    $totalVotes = (int) $poll['total_votes'];
                    $options    = $optionsByPoll[$pollId] ?? [];
                    $hasVoted   = array_key_exists($pollId, $myVotes);
                ?>
                <div class="poll-card" id="poll-<?= $pollId ?>">
                    <span class="type-ribbon ribbon-poll">Poll</span>
                    <?php if (!empty($poll['image_url'])): ?>
                        <img class="poll-image" src="<?= e($poll['image_url']) ?>" alt="<?= e($poll['title']) ?>">
                    <?php else: ?>
                        <div class="poll-image poll-placeholder"><?= e(strtoupper(mb_substr($poll['title'], 0, 1))) ?></div>
                    <?php endif; ?>

                    <div class="poll-body">
                        <h2 class="poll-title"><?= e($poll['title']) ?></h2>
                        <div class="poll-meta">
                            <?php if (!empty($poll['category_name'])): ?>
                                <span class="poll-category"><?= e($poll['category_name']) ?></span>
                            <?php endif; ?>
                            <?php renderMiiPfp($poll, 'poll-' . $pollId, $miis); ?><span class="poll-author">by <?= e($poll['username']) ?></span>
                            <span class="poll-votes"><?= $totalVotes ?> vote<?= $totalVotes === 1 ? '' : 's' ?></span>
                            <?php if ($isLoggedIn && ((int) $poll['user_id'] === $userId || ($_SESSION['role'] ?? '') === 'admin')): ?>
                                <form class="delete-form" method="post" action="polls.php<?= $filterCategory > 0 ? '?category=' . $filterCategory : '' ?>" onsubmit="return confirm('Delete this poll?');">
                                    <input type="hidden" name="action" value="delete_item">
                                    <input type="hidden" name="kind" value="poll">
                                    <input type="hidden" name="item_id" value="<?= $pollId ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="poll-options">
                            <?php foreach ($options as $opt): ?>
                                <?php
                                    $optId    = (int) $opt['id'];
                                    $optVotes = (int) $opt['option_votes'];
                                    $percent  = $totalVotes > 0 ? (int) round($optVotes * 100 / $totalVotes) : 0;
                                ?>
                                <?php if ($isLoggedIn && !$hasVoted): ?>
                                    <form class="vote-form" method="post" action="polls.php<?= $filterCategory > 0 ? '?category=' . $filterCategory : '' ?>">
                                        <input type="hidden" name="action" value="vote">
                                        <input type="hidden" name="poll_id" value="<?= $pollId ?>">
                                        <button class="vote-button" type="submit" name="option_id" value="<?= $optId ?>"><?= e($opt['name']) ?></button>
                                    </form>
                                <?php else: ?>
                                    <div class="result<?= $hasVoted && $myVotes[$pollId] === $optId ? ' result-mine' : '' ?>">
                                        <span class="result-name"><?= e($opt['name']) ?></span>
                                        <div class="result-bar">
                                            <div class="result-fill" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <span class="result-percent"><?= $percent ?>%</span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php renderComments('poll', $pollId, $commentsByTarget, $isLoggedIn, $filterCategory); ?>
                    </div>
                </div>
            <?php else: ?>
                <?php
                    $post   = $item['data'];
                    $postId = (int) $post['id'];
                ?>
                <div class="poll-card" id="post-<?= $postId ?>">
                    <span class="type-ribbon ribbon-post">Post</span>
                    <?php if (!empty($post['image_url'])): ?>
                        <img class="poll-image" src="<?= e($post['image_url']) ?>" alt="<?= e($post['title']) ?>">
                    <?php else: ?>
                        <div class="poll-image poll-placeholder"><?= e(strtoupper(mb_substr($post['title'], 0, 1))) ?></div>
                    <?php endif; ?>

                    <div class="poll-body">
                        <h2 class="poll-title"><?= e($post['title']) ?></h2>
                        <div class="poll-meta">
                            <?php if (!empty($post['category_name'])): ?>
                                <span class="poll-category"><?= e($post['category_name']) ?></span>
                            <?php endif; ?>
                            <?php renderMiiPfp($post, 'post-' . $postId, $miis); ?><span class="poll-author">by <?= e($post['username']) ?></span>
                            <?php if ($isLoggedIn && ((int) $post['user_id'] === $userId || ($_SESSION['role'] ?? '') === 'admin')): ?>
                                <form class="delete-form" method="post" action="polls.php<?= $filterCategory > 0 ? '?category=' . $filterCategory : '' ?>" onsubmit="return confirm('Delete this post?');">
                                    <input type="hidden" name="action" value="delete_item">
                                    <input type="hidden" name="kind" value="post">
                                    <input type="hidden" name="item_id" value="<?= $postId ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($post['content'])): ?>
                            <p class="post-content"><?= nl2br(e($post['content'])) ?></p>
                        <?php endif; ?>

                        <?php renderComments('post', $postId, $commentsByTarget, $isLoggedIn, $filterCategory); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<footer class="bottom">
    <a class="bottom-button home" href="index.php">Home</a>
    <div class="username-block">
        <div class="username"><?= e($isLoggedIn ? $username : 'Guest') ?></div>
        <?php if ($isLoggedIn): ?>
            <a class="logout-link" href="logout.php">Logout</a>
        <?php endif; ?>
    </div>
    <a class="bottom-button profile" href="<?= $isLoggedIn ? 'profile.php' : 'login.php' ?>"><?= $isLoggedIn ? 'Profile' : 'Login' ?></a>
</footer>
<button class="menu-button" type="button">Menu</button>

<script src="js/menu.js" defer></script>
<script src="js/mii_pfp.js" defer></script>
<script>window.FEED_MIIS = <?= json_encode($miis) ?></script>
</body>
</html>
