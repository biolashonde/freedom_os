USE freedomos;

DELETE FROM devotionals
WHERE user_id IS NULL AND day_number BETWEEN 1 AND 100;

CREATE TEMPORARY TABLE devotional_seed_days (day_number SMALLINT PRIMARY KEY);

INSERT INTO devotional_seed_days (day_number) VALUES
(1),(2),(3),(4),(5),(6),(7),(8),(9),(10),
(11),(12),(13),(14),(15),(16),(17),(18),(19),(20),
(21),(22),(23),(24),(25),(26),(27),(28),(29),(30),
(31),(32),(33),(34),(35),(36),(37),(38),(39),(40),
(41),(42),(43),(44),(45),(46),(47),(48),(49),(50),
(51),(52),(53),(54),(55),(56),(57),(58),(59),(60),
(61),(62),(63),(64),(65),(66),(67),(68),(69),(70),
(71),(72),(73),(74),(75),(76),(77),(78),(79),(80),
(81),(82),(83),(84),(85),(86),(87),(88),(89),(90),
(91),(92),(93),(94),(95),(96),(97),(98),(99),(100);

INSERT INTO devotionals (
  user_id,
  day_number,
  title,
  theme,
  scripture_ref,
  scripture_text,
  body,
  prayer,
  ai_generated,
  published_date
)
SELECT
  NULL,
  day_number,
  CONCAT('Day ', day_number, ': ', CASE (day_number - 1) % 20
    WHEN 0 THEN 'You Are Not Your Urge'
    WHEN 1 THEN 'The Way Of Escape'
    WHEN 2 THEN 'Tell The Truth Early'
    WHEN 3 THEN 'Small Obedience Counts'
    WHEN 4 THEN 'After A Reset'
    WHEN 5 THEN 'Build What You Are For'
    WHEN 6 THEN 'You Are Still Becoming'
    WHEN 7 THEN 'Leave The Room'
    WHEN 8 THEN 'Grace For The Next Step'
    WHEN 9 THEN 'A Clean Imagination'
    WHEN 10 THEN 'When Shame Gets Loud'
    WHEN 11 THEN 'Practice The Pause'
    WHEN 12 THEN 'No Secret Is Stronger Than Light'
    WHEN 13 THEN 'Strength In Community'
    WHEN 14 THEN 'The Body Needs Mercy'
    WHEN 15 THEN 'Choose The Doorway'
    WHEN 16 THEN 'Hope Has Habits'
    WHEN 17 THEN 'Guard The Small Gate'
    WHEN 18 THEN 'Return Quickly'
    ELSE 'Freedom Is For Love'
  END),
  CASE (day_number - 1) % 10
    WHEN 0 THEN 'Identity'
    WHEN 1 THEN 'Action'
    WHEN 2 THEN 'Accountability'
    WHEN 3 THEN 'Faithfulness'
    WHEN 4 THEN 'Grace'
    WHEN 5 THEN 'Purpose'
    WHEN 6 THEN 'Hope'
    WHEN 7 THEN 'Escape'
    WHEN 8 THEN 'Renewal'
    ELSE 'Love'
  END,
  CASE (day_number - 1) % 20
    WHEN 0 THEN 'Romans 8:1'
    WHEN 1 THEN '1 Corinthians 10:13'
    WHEN 2 THEN 'James 5:16'
    WHEN 3 THEN 'Luke 16:10'
    WHEN 4 THEN 'Psalm 34:18'
    WHEN 5 THEN 'Ephesians 2:10'
    WHEN 6 THEN 'Philippians 1:6'
    WHEN 7 THEN '2 Timothy 2:22'
    WHEN 8 THEN 'Hebrews 4:16'
    WHEN 9 THEN 'Romans 12:2'
    WHEN 10 THEN 'Psalm 103:12'
    WHEN 11 THEN 'Psalm 46:10'
    WHEN 12 THEN 'John 8:32'
    WHEN 13 THEN 'Ecclesiastes 4:9-10'
    WHEN 14 THEN '1 Corinthians 6:19-20'
    WHEN 15 THEN 'Proverbs 4:23'
    WHEN 16 THEN 'Romans 15:13'
    WHEN 17 THEN 'Matthew 5:8'
    WHEN 18 THEN 'Micah 7:8'
    ELSE 'Galatians 5:13'
  END,
  CASE (day_number - 1) % 20
    WHEN 0 THEN 'There is therefore now no condemnation for those who are in Christ Jesus.'
    WHEN 1 THEN 'God is faithful, and he will not let you be tempted beyond your ability, but with the temptation he will also provide the way of escape.'
    WHEN 2 THEN 'Confess your sins to one another and pray for one another, that you may be healed.'
    WHEN 3 THEN 'One who is faithful in a very little is also faithful in much.'
    WHEN 4 THEN 'The Lord is near to the brokenhearted and saves the crushed in spirit.'
    WHEN 5 THEN 'For we are his workmanship, created in Christ Jesus for good works.'
    WHEN 6 THEN 'He who began a good work in you will bring it to completion at the day of Jesus Christ.'
    WHEN 7 THEN 'Flee youthful passions and pursue righteousness, faith, love, and peace.'
    WHEN 8 THEN 'Let us then with confidence draw near to the throne of grace.'
    WHEN 9 THEN 'Be transformed by the renewal of your mind.'
    WHEN 10 THEN 'As far as the east is from the west, so far does he remove our transgressions from us.'
    WHEN 11 THEN 'Be still, and know that I am God.'
    WHEN 12 THEN 'You will know the truth, and the truth will set you free.'
    WHEN 13 THEN 'Two are better than one, because they have a good reward for their toil.'
    WHEN 14 THEN 'Your body is a temple of the Holy Spirit within you.'
    WHEN 15 THEN 'Keep your heart with all vigilance, for from it flow the springs of life.'
    WHEN 16 THEN 'May the God of hope fill you with all joy and peace in believing.'
    WHEN 17 THEN 'Blessed are the pure in heart, for they shall see God.'
    WHEN 18 THEN 'When I fall, I shall rise; when I sit in darkness, the Lord will be a light to me.'
    ELSE 'Through love serve one another.'
  END,
  CONCAT(
    'Today is day ', day_number, ' of a steady recovery path. Do not measure this day by how loud the urge feels; measure it by the next faithful action you can take. Freedom often grows through ordinary obedience: closing the tab, standing up, changing rooms, telling the truth, drinking water, praying honestly, and returning before shame can build a wall.\n\n',
    CASE (day_number - 1) % 10
      WHEN 0 THEN 'Your identity is not negotiated by temptation. You are not an appetite, a browser history, a hidden habit, or a worst moment. In Christ, you are being named by grace and trained by love.'
      WHEN 1 THEN 'Action matters before emotion changes. If pressure is rising, move first and analyze later. The way of escape may look simple, but simple obedience is still holy.'
      WHEN 2 THEN 'Secrecy makes temptation feel larger than it is. One honest message, one partner check-in, or one SOS log can puncture the lie that you must fight alone.'
      WHEN 3 THEN 'Small faithfulness is not small to God. A clean choice that nobody applauds is still a seed of freedom. Mark it, thank God for it, and repeat it.'
      WHEN 4 THEN 'A reset is serious, but it is not your identity. Learn clearly, repair quickly, and return to the light without performing despair.'
      WHEN 5 THEN 'Freedom is not only leaving something behind; it is becoming available for worship, service, friendship, work, rest, and love. Build one thing today that your future self can live inside.'
      WHEN 6 THEN 'Hope is not pretending the fight is easy. Hope is trusting that God keeps working while you keep showing up. Uneven progress is still progress when you keep returning.'
      WHEN 7 THEN 'Your environment is part of your discipleship. Guard the hour, the room, the device, and the path your mind usually takes. A wise boundary is not weakness.'
      WHEN 8 THEN 'Renewal often begins with attention. Feed your mind with what is true, beautiful, useful, and clean. Starve the old loop by choosing a better focus.'
      ELSE 'Freedom is for love. The goal is not merely to avoid a fall, but to become more present, honest, generous, and whole.'
    END,
    '\n\nTake one concrete step now. Keep it plain enough to obey.'
  ),
  CONCAT(
    CASE (day_number - 1) % 10
      WHEN 0 THEN 'Father, remind me who I am in Christ. When temptation tries to rename me, answer louder with grace.'
      WHEN 1 THEN 'Lord, show me the way of escape and give me humility to take it quickly, even if it looks ordinary.'
      WHEN 2 THEN 'God, give me courage to tell the truth early. Teach me to receive support without shame.'
      WHEN 3 THEN 'Father, help me value small faithfulness. Build a steadier life in me one clean choice at a time.'
      WHEN 4 THEN 'Lord, meet me with mercy. Help me learn without cruelty and return without delay.'
      WHEN 5 THEN 'God, show me what freedom is for. Give me strength to build a life that reflects your goodness.'
      WHEN 6 THEN 'God of hope, keep working in me. Help me notice progress and keep walking when the pace feels slow.'
      WHEN 7 THEN 'Lord, give me wisdom to guard my heart, my time, my body, and my devices today.'
      WHEN 8 THEN 'Holy Spirit, renew my mind. Turn my attention toward truth, beauty, and the next clean step.'
      ELSE 'Jesus, make my freedom fruitful in love. Help me use this day to serve, connect, and live honestly.'
    END,
    ' Amen.'
  ),
  0,
  CURDATE()
FROM devotional_seed_days
ORDER BY day_number;

DROP TEMPORARY TABLE devotional_seed_days;
