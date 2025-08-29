<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Admin - Like & View Statistics</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 2.5rem;
            font-weight: 300;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .data-section {
            margin-bottom: 30px;
        }

        .data-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .refresh-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .refresh-btn:hover {
            background: #45a049;
        }

        .empty-message {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }

        .file-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }

        .file-info h3 {
            color: #1976d2;
            margin-bottom: 10px;
        }

        .file-info p {
            color: #424242;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Gallery Admin Dashboard</h1>
        
        <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Data</button>

        <?php
        // Create data directory if it doesn't exist
        $dataDir = 'data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        // File paths
        $likesFile = $dataDir . '/likes.txt';
        $likedByFile = $dataDir . '/liked_by.txt';
        $viewsFile = $dataDir . '/views.txt';
        $viewHistoryFile = $dataDir . '/view_history.txt';
        $lastUpdateFile = $dataDir . '/last_update.txt';

        // Initialize files if they don't exist
        if (!file_exists($likesFile)) {
            file_put_contents($likesFile, '{}');
        }
        if (!file_exists($likedByFile)) {
            file_put_contents($likedByFile, '{}');
        }
        if (!file_exists($viewsFile)) {
            file_put_contents($viewsFile, '{}');
        }
        if (!file_exists($viewHistoryFile)) {
            file_put_contents($viewHistoryFile, '{}');
        }
        if (!file_exists($lastUpdateFile)) {
            file_put_contents($lastUpdateFile, time());
        }

        // Read data
        $likesData = json_decode(file_get_contents($likesFile), true) ?: [];
        $likedByData = json_decode(file_get_contents($likedByFile), true) ?: [];
        $viewsData = json_decode(file_get_contents($viewsFile), true) ?: [];
        $viewHistoryData = json_decode(file_get_contents($viewHistoryFile), true) ?: [];
        $lastUpdate = (int)file_get_contents($lastUpdateFile);

        // Calculate statistics
        $totalImages = count(array_unique(array_merge(array_keys($likesData), array_keys($viewsData))));
        $totalLikes = array_sum($likesData);
        $totalViews = array_sum($viewsData);
        $uniqueUsers = 0;
        $userSet = [];
        foreach ($likedByData as $imageId => $users) {
            foreach ($users as $user) {
                $userSet[$user] = true;
            }
        }
        foreach ($viewHistoryData as $imageId => $users) {
            foreach ($users as $user => $time) {
                $userSet[$user] = true;
            }
        }
        $uniqueUsers = count($userSet);
        ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Images</h3>
                <div class="stat-number"><?php echo $totalImages; ?></div>
                <p>Images with activity</p>
            </div>
            <div class="stat-card">
                <h3>Total Likes</h3>
                <div class="stat-number"><?php echo $totalLikes; ?></div>
                <p>Across all images</p>
            </div>
            <div class="stat-card">
                <h3>Total Views</h3>
                <div class="stat-number"><?php echo $totalViews; ?></div>
                <p>Unique views counted</p>
            </div>
            <div class="stat-card">
                <h3>Unique Users</h3>
                <div class="stat-number"><?php echo $uniqueUsers; ?></div>
                <p>Active users</p>
            </div>
        </div>

        <!-- File Information -->
        <div class="file-info">
            <h3>📁 Data Files Information</h3>
            <p><strong>Last Update:</strong> <?php echo date('Y-m-d H:i:s', $lastUpdate); ?></p>
            <p><strong>Likes File Size:</strong> <?php echo number_format(filesize($likesFile)) . ' bytes'; ?></p>
            <p><strong>Views File Size:</strong> <?php echo number_format(filesize($viewsFile)) . ' bytes'; ?></p>
            <p><strong>Data Directory:</strong> <?php echo realpath($dataDir); ?></p>
        </div>

        <!-- Top Images by Likes -->
        <div class="data-section">
            <h2>🔥 Top Images by Likes</h2>
            <?php if (!empty($likesData)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Image ID</th>
                            <th>Likes</th>
                            <th>Views</th>
                            <th>Like/View Ratio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        arsort($likesData);
                        $rank = 1;
                        foreach (array_slice($likesData, 0, 10) as $imageId => $likes): 
                            $views = $viewsData[$imageId] ?? 0;
                            $ratio = $views > 0 ? round(($likes / $views) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>#<?php echo $rank; ?></td>
                            <td><?php echo htmlspecialchars($imageId); ?></td>
                            <td><?php echo $likes; ?></td>
                            <td><?php echo $views; ?></td>
                            <td><?php echo $ratio; ?>%</td>
                        </tr>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No like data available yet.</div>
            <?php endif; ?>
        </div>

        <!-- Top Images by Views -->
        <div class="data-section">
            <h2>👁️ Top Images by Views</h2>
            <?php if (!empty($viewsData)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Image ID</th>
                            <th>Views</th>
                            <th>Likes</th>
                            <th>Like/View Ratio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        arsort($viewsData);
                        $rank = 1;
                        foreach (array_slice($viewsData, 0, 10) as $imageId => $views): 
                            $likes = $likesData[$imageId] ?? 0;
                            $ratio = $views > 0 ? round(($likes / $views) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>#<?php echo $rank; ?></td>
                            <td><?php echo htmlspecialchars($imageId); ?></td>
                            <td><?php echo $views; ?></td>
                            <td><?php echo $likes; ?></td>
                            <td><?php echo $ratio; ?>%</td>
                        </tr>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No view data available yet.</div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="data-section">
            <h2>📊 Recent Activity (Last 20 Images)</h2>
            <?php 
            $allImageIds = array_unique(array_merge(array_keys($likesData), array_keys($viewsData)));
            $recentImages = array_slice($allImageIds, -20);
            ?>
            <?php if (!empty($recentImages)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image ID</th>
                            <th>Likes</th>
                            <th>Views</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($recentImages) as $imageId): 
                            $likes = $likesData[$imageId] ?? 0;
                            $views = $viewsData[$imageId] ?? 0;
                            $status = '';
                            if ($likes > 0 && $views > 0) {
                                $status = 'Active';
                            } elseif ($likes > 0) {
                                $status = 'Liked Only';
                            } elseif ($views > 0) {
                                $status = 'Viewed Only';
                            } else {
                                $status = 'No Activity';
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($imageId); ?></td>
                            <td><?php echo $likes; ?></td>
                            <td><?php echo $views; ?></td>
                            <td><?php echo $status; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No recent activity data available.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>