USE freedomos;

CREATE TABLE IF NOT EXISTS app_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(80) NOT NULL UNIQUE,
  setting_value TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sos_resources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('music','game','resource') NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT,
  url VARCHAR(500) DEFAULT NULL,
  duration_label VARCHAR(40) DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sos_resources (type, title, description, url, duration_label, active, sort_order) VALUES
('music', 'Instrumental worship piano', 'Soft piano worship for breathing and slowing down.', 'https://www.youtube.com/results?search_query=instrumental+worship+piano+peaceful', '5-15 min', 1, 10),
('music', 'Peaceful hymns instrumental', 'Gentle hymn instrumentals without lyrical distraction.', 'https://www.youtube.com/results?search_query=peaceful+hymns+instrumental', '5-15 min', 1, 20),
('music', 'Acoustic gospel worship', 'Warm acoustic worship for grounding and hope.', 'https://www.youtube.com/results?search_query=acoustic+gospel+worship+calm', '5-20 min', 1, 30),
('music', 'Soft gospel choir', 'Choir worship to help move attention away from the urge.', 'https://www.youtube.com/results?search_query=soft+gospel+choir+worship', '5-20 min', 1, 40),
('music', 'Christian meditation music', 'Low-intensity instrumental music for prayer and calm.', 'https://www.youtube.com/results?search_query=christian+meditation+music+instrumental', '10-30 min', 1, 50),
('music', 'Soaking worship instrumental', 'Quiet background worship for deep breathing.', 'https://www.youtube.com/results?search_query=soaking+worship+instrumental', '10-30 min', 1, 60),
('music', 'Psalm worship instrumental', 'Instrumental psalm-inspired worship.', 'https://www.youtube.com/results?search_query=psalm+worship+instrumental', '5-20 min', 1, 70),
('music', 'Calm gospel piano', 'Gospel piano for a slower nervous system.', 'https://www.youtube.com/results?search_query=calm+gospel+piano', '5-20 min', 1, 80),
('music', 'Worship guitar instrumental', 'Soft guitar worship for stepping away from screens.', 'https://www.youtube.com/results?search_query=worship+guitar+instrumental+peaceful', '5-20 min', 1, 90),
('music', 'Scripture meditation audio', 'Scripture-based calm listening.', 'https://www.youtube.com/results?search_query=scripture+meditation+audio+calm', '5-20 min', 1, 100),
('music', 'Peaceful Christian lofi', 'Low-energy Christian lofi beats for redirecting attention.', 'https://www.youtube.com/results?search_query=christian+lofi+peaceful', '10-30 min', 1, 110),
('music', 'Morning worship instrumental', 'Bright but gentle worship for a reset.', 'https://www.youtube.com/results?search_query=morning+worship+instrumental+peaceful', '5-20 min', 1, 120),
('music', 'Evening prayer music', 'Quiet prayer music for late-night urges.', 'https://www.youtube.com/results?search_query=evening+prayer+music+christian', '10-30 min', 1, 130),
('music', 'Gospel worship playlist', 'A gospel worship search when you need words of hope.', 'https://www.youtube.com/results?search_query=gospel+worship+playlist+calm', '10-30 min', 1, 140),
('music', 'Instrumental praise break', 'Gentle praise instrumentals to change emotional direction.', 'https://www.youtube.com/results?search_query=instrumental+praise+worship+music', '5-20 min', 1, 150),
('music', 'Calming harp worship', 'Harp-style worship instrumentals for slowing down.', 'https://www.youtube.com/results?search_query=harp+worship+instrumental+peaceful', '5-20 min', 1, 160),
('music', 'Rain and worship piano', 'Rain ambience with worship piano for nervous-system reset.', 'https://www.youtube.com/results?search_query=rain+worship+piano+instrumental', '10-30 min', 1, 170),
('music', 'Deep prayer instrumental', 'Undistracting music for five minutes of prayer.', 'https://www.youtube.com/results?search_query=deep+prayer+instrumental+christian', '5-20 min', 1, 180),
('music', 'Hopeful gospel instrumental', 'Hopeful gospel instrumentals when shame feels loud.', 'https://www.youtube.com/results?search_query=hopeful+gospel+instrumental', '5-20 min', 1, 190),
('music', 'Peace before sleep worship', 'Slow worship music for late-night protection.', 'https://www.youtube.com/results?search_query=peaceful+worship+music+sleep', '10-30 min', 1, 200),
('game', 'Verse scramble', 'Put a recovery scripture back in order.', NULL, '2 min', 1, 300),
('game', 'Bible memory match', 'Match hope words with short scripture anchors.', NULL, '3 min', 1, 310),
('game', 'Gratitude countdown', 'Name five gifts, four people, three clean choices, two hopes, and one next step.', NULL, '3 min', 1, 320),
('game', 'Psalm finder', 'Pick one psalm theme and read a short prompt.', NULL, '2 min', 1, 330),
('resource', 'Box breathing', 'Breathe in 4, hold 4, out 4, hold 4. Repeat five rounds.', NULL, '2 min', 1, 400),
('resource', 'Cold water reset', 'Drink water, wash your face, and change rooms.', NULL, '2 min', 1, 410),
('resource', 'Text someone safe', 'Send: I am under pressure. Please check on me in 10 minutes.', NULL, '1 min', 1, 420),
('resource', 'Walk outside', 'Leave the room and walk until the wave loses intensity.', NULL, '5 min', 1, 430),
('resource', 'Device distance', 'Put the device across the room and open SOS on another screen if possible.', NULL, '1 min', 1, 440)
ON DUPLICATE KEY UPDATE title = VALUES(title);
