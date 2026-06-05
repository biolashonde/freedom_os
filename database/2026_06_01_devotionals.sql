USE freedomos;

CREATE TABLE IF NOT EXISTS devotionals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  day_number SMALLINT DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  theme VARCHAR(80) DEFAULT 'Identity',
  scripture_ref VARCHAR(60),
  scripture_text TEXT,
  body TEXT,
  prayer TEXT,
  ai_generated TINYINT(1) DEFAULT 0,
  published_date DATE DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_global_day (user_id, day_number)
);

INSERT INTO devotionals (user_id, day_number, title, theme, scripture_ref, scripture_text, body, prayer, ai_generated, published_date) VALUES
(NULL, 1, 'You Are Not Your Urge', 'Identity', 'Romans 8:1', 'There is therefore now no condemnation for those who are in Christ Jesus.', 'An urge can feel loud, but it is not lord. It can make promises, raise alarms, and ask for your attention, but it cannot name you. In Christ, your identity is not built from yesterday, your browser history, your worst habit, or your strongest temptation. Your identity begins with grace.\n\nToday, practice separating signal from self. You may feel pressure, but you are not pressure. You may feel weakness, but you are not abandoned. You can pause, breathe, change location, and choose the next clean step.', 'Father, remind me that condemnation is not your voice. Help me receive grace and take the next faithful step today.', 0, CURDATE()),
(NULL, 2, 'The Way Of Escape', 'Action', '1 Corinthians 10:13', 'God is faithful, and he will not let you be tempted beyond your ability, but with the temptation he will also provide the way of escape.', 'Freedom often arrives as a practical doorway. A walk. A text. A cold drink of water. A decision to leave the room. The way of escape may not feel dramatic, but it is holy.\n\nDo not wait until the urge is gone before you move. Movement is part of resistance. Today, decide your first escape action before pressure rises. Make it plain enough that you can obey it when your mind feels crowded.', 'God, show me the way of escape and give me the humility to take it quickly.', 0, CURDATE()),
(NULL, 3, 'Tell The Truth Early', 'Accountability', 'James 5:16', 'Confess your sins to one another and pray for one another, that you may be healed.', 'Shame says, hide until you are stronger. Wisdom says, speak while the pressure is still small. You do not need to perform a crisis to deserve support. You can tell the truth early.\n\nAccountability is not surveillance. It is shared courage. One honest message can interrupt an entire pattern. Today, choose one person or one written check-in where you will tell the truth without dressing it up.', 'Lord, give me courage to be honest before I am overwhelmed. Teach me to receive support without shame.', 0, CURDATE()),
(NULL, 4, 'Small Obedience Counts', 'Purpose', 'Luke 16:10', 'One who is faithful in a very little is also faithful in much.', 'A clean day is not empty. It is full of small obediences: closing the tab, making the call, taking the walk, sleeping on time, praying badly but honestly. These are not small to God.\n\nYou are rebuilding trust with yourself one choice at a time. Do not despise the quiet win. Mark it. Thank God for it. Then take the next one.', 'Father, help me value small faithfulness. Build a steadier life in me one choice at a time.', 0, CURDATE()),
(NULL, 5, 'After A Reset', 'Grace', 'Psalm 34:18', 'The Lord is near to the brokenhearted and saves the crushed in spirit.', 'A reset is serious, but it is not the end of your story. The enemy would love to turn a stumble into a spiral. Grace calls you back to the light quickly.\n\nTell the truth. Clean up the environment. Check in. Ask what happened without cruelty. Then begin again. The Lord is near, not waiting at a distance for you to become impressive.', 'God, meet me with mercy. Help me learn clearly, repair quickly, and keep walking with you.', 0, CURDATE()),
(NULL, 6, 'Build What You Are For', 'Purpose', 'Ephesians 2:10', 'For we are his workmanship, created in Christ Jesus for good works.', 'Freedom is not only leaving something behind. It is becoming available for love, work, worship, friendship, service, and joy. If recovery is only subtraction, your heart will stay hungry.\n\nDo one constructive thing today. Make something, serve someone, move your body, study, clean, plan, worship. Build evidence that your life is bigger than the old loop.', 'Lord, show me what I am for. Give me energy to build a life that reflects your goodness.', 0, CURDATE()),
(NULL, 7, 'You Are Still Becoming', 'Hope', 'Philippians 1:6', 'He who began a good work in you will bring it to completion at the day of Jesus Christ.', 'Progress may feel uneven, but God is not confused by the pace of your healing. He is patient, truthful, and persistent. The work he begins, he continues.\n\nLook back over the week and name one place you resisted, one place you learned, and one place you need support. Hope gets stronger when it becomes specific.', 'Father, thank you for continuing your work in me. Help me notice progress and keep surrendering the places still under construction.', 0, CURDATE())
ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body), prayer = VALUES(prayer);
