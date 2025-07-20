<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Process the vote (without saving to database)
    // You can add database logic here later when needed
    
    // Set success flag in session
    $_SESSION['vote_success'] = true;
    
    // Redirect to user dashboard
    header("Location: user_dashboard.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <title>TECHVote</title>
    <style>
        body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #2d3232;
    color: #ffffff;
    line-height: 1.6;
}

.dashboard-icon-button {
    color: white;
    text-decoration: none;
    font-size: 24px;
    padding: 6px 10px;
    border: 1px solid white;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    transition: background-color 0.3s, color 0.3s, border-color 0.3s;
}

.dashboard-icon-button:hover {
    background-color: #45a049;
    color: white;
    border-color: #45a049;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #202424;
    padding: 14px 24px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.4);
}
.back-button {
    color: white;
    font-size: 24px;
    text-decoration: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    transition: color 0.3s;
}

.back-button:hover {
    color: #45a049;
}

header h1 {
    margin: 0;
    font-size: 22px;
    letter-spacing: 1px;
}

nav a {
    color: #ccc;
    text-decoration: none;
    margin: 0 12px;
    font-size: 15px;
    transition: color 0.3s;
}

nav a:hover {
    color: #45a049;
}

.vote-button {
    background-color: #45a049;
    color: white;
    border: none;
    padding: 8px 16px;
    cursor: pointer;
    border-radius: 6px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.vote-button:hover {
    background-color: #36923b;
}

.hero {
    height: 100vh;
    padding: 140px 20px 40px;
    background-color: #2d3232;
    text-align: center;
    box-sizing: border-box;
    overflow: hidden;
    transition: height 0.8s ease, opacity 0.8s ease, padding 0.8s ease;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.hero h2 {
    max-width: 700px;
    margin: 0 auto 20px;
    font-size: 28px;
    font-weight: 700;
    line-height: 1.3;
}

.hero p {
    max-width: 500px;
    margin: 20px auto 30px;
    font-size: 16px;
    color: #aaa;
}

.start-button {
    background-color: #45a049;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
    display: block;
    margin: 20px auto;
    width: auto;
    max-width: 200px;
    transition: background-color 0.3s, transform 0.2s;
}

.start-button:hover {
    background-color: #36923b;
    transform: scale(1.02);
}

.arrow {
    font-size: 28px;
    color: #45a049;
    margin-top: 30px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(6px);
    }
    60% {
        transform: translateY(3px);
    }
}

.hero.collapsed {
    height: 0;
    padding: 0;
    opacity: 0;
}

#voting-section {
    display: none;
    opacity: 0;
    transition: opacity 0.8s ease;
    padding-top: 100px;
    box-sizing: border-box;
}

#voting-section.visible {
    display: block;
    opacity: 1;
}

.council-section {
    background-color: #1e2222;
    padding: 50px 20px;
    border-top: 1px solid #333;
}

.council-section h3 {
    text-align: center;
    margin-bottom: 20px;
    text-transform: uppercase;
    font-size: 20px;
    letter-spacing: 1px;
}

.council-description {
    max-width: 700px;
    margin: 0 auto 40px;
    font-size: 15px;
    color: #ccc;
    text-align: center;
    line-height: 1.6;
}

.council-description ul {
    list-style: disc inside;
    text-align: left;
    margin: 20px auto;
    max-width: 600px;
    padding-left: 20px;
}

form {
    background-color: #3a4a4a;
    padding: 30px;
    max-width: 950px;
    margin: 0 auto 60px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.positions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 24px;
}

.position {
    background-color: #4a5a5a;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    transition: transform 0.2s;
}

.position:hover {
    transform: translateY(-2px);
}

.position h4 {
    margin-top: 0;
    margin-bottom: 12px;
    font-size: 17px;
    text-transform: uppercase;
    border-bottom: 1px solid #ccc;
    padding-bottom: 6px;
}

.position label {
    display: block;
    margin: 6px 0;
    cursor: pointer;
    font-size: 14px;
    transition: color 0.2s;
}

.position label:hover {
    color: #45a049;
}

.submit-vote {
    display: block;
    margin: 40px auto 0;
    background-color: #45a049;
    color: white;
    border: none;
    padding: 14px 36px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 17px;
    transition: background-color 0.3s, transform 0.2s;
}

.submit-vote:hover {
    background-color: #36923b;
    transform: scale(1.02);
}

@media (max-width: 600px) {
    .hero h2 {
        font-size: 22px;
    }

    header h1 {
        font-size: 18px;
    }

    nav a {
        font-size: 13px;
        margin: 0 6px;
    }
}

    </style>
</head>
<body>
    <header>
        <h1>TECHVote</h1>
        <nav>
            <!-- Back to Dashboard   but only in user_dashboard.php-->
            <a href="user_dashboard.php" class="dashboard-icon-button" title="Back to Dashboard">
             <i class="bi bi-arrow-bar-left"></i>
            </a>
        </nav>
    </header>

    <section class="hero" id="hero">
        <h2>Voting wisely isn't just a right; it's a responsibility to your peers, your education, and the future you want to create</h2>
        <p>A wise vote values character over charisma, and substance over slogans</p>
        <button class="start-button" onclick="showVoting()">START VOTING</button>
        <div class="arrow">↓</div>
    </section>

    <section class="council-section" id="voting-section">
        <h3>STUDENT COORDINATING COUNCIL</h3>
        <div class="council-description">
            <p>The Student Coordinating Council serves as the central hub of FEU Tech's student organizations, facilitating collaboration, leadership development, and institutional representation. Our elected representatives work to:</p>
            <ul>
                <li>Unify Voices: Bridge communication between student groups and administration</li>
                <li>Drive Initiatives: Champion academic, welfare, and extracurricular programs</li>
                <li>Maintain Standards: Uphold fair governance practices across campus organizations</li>
            </ul>
        </div>

        <form id="vote-form" method="POST">
            <div class="positions">
                <?php
                $positions = ['President', 'VP Internal', 'VP External', 'Secretary', 'P.R.O', 'Treasurer', 'Auditor'];
                $candidates = ['Spongebob Square Pants', 'Patrick Star', 'Sandy Cheeks', 'Squid Ward', 'Abstain'];

                foreach ($positions as $pos) {
                    echo "<div class='position'>";
                    echo "<h4>$pos</h4>";
                    foreach ($candidates as $cand) {
                        $inputName = strtolower(str_replace(' ', '_', $pos));
                        $inputValue = htmlspecialchars($cand);
                        echo "<label><input type='radio' name='$inputName' value='$inputValue' required> $cand</label>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
            <button type="submit" class="submit-vote">SUBMIT VOTE</button>
        </form>
    </section>

    <script>
        function showVoting() {
            const hero = document.getElementById('hero');
            const voting = document.getElementById('voting-section');

            hero.classList.add('collapsed');
            setTimeout(() => {
                voting.classList.add('visible');
                voting.scrollIntoView({ behavior: 'smooth' });
            }, 800);
        }
    </script>
</body>
</html>