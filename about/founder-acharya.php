<?php
$pageTitle = 'Founder Acharya';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/founder-acharya.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Founder Acharya</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="about">About Us</a><span>›</span><span>Founder Acharya</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/founder-acharya.jpg" alt="Srila Prabhupada" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="about-intro-text reveal">
        <div class="decorative-line left"></div>
        <h2>His Divine Grace A.C. Bhaktivedanta Swami Prabhupada</h2>
        <p style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;font-style:italic;color:var(--primary);margin-bottom:var(--space-lg);line-height:1.8;">
          nama om vishnu-padaya krishna-preshthaya bhutale<br>
          srimate bhaktivedanta-swamin iti namine<br>
          namas te saraswate deve gaura-vani-pracharine<br>
          nirvishesha shunyavadi pashchatya desha tarine
        </p>
        <p>His Divine Grace A.C. Bhaktivedanta Swami Prabhupāda appeared in this world in 1896 in Calcutta, India. He first met his spiritual master, Śrīla Bhaktisiddhānta Sarasvatī Gosvāmī, in Calcutta in 1922. Bhaktisiddhānta Sarasvatī, a prominent religious scholar and the founder of sixty-four Gauḍīya Maṭhas (Vedic institutes), liked this educated young man and convinced him to dedicate his life to teaching Vedic knowledge. Śrīla Prabhupāda became his student, and eleven years later (1933) at Allahabad he became his formally initiated disciple.</p>
        <p>At their first meeting, in 1922, Śrīla Bhaktisiddhānta Sarasvatī Ṭhākura requested Śrīla Prabhupāda to broadcast Vedic knowledge through the English language. In the years that followed, Śrīla Prabhupāda wrote a commentary on the Bhagavad-gītā, assisted the Gauḍīya Maṭha in its work and, in 1944, without assistance, started an English fortnightly magazine, edited it, typed the manuscripts and checked the galley proofs. He even distributed the individual copies and struggled to maintain the publication. Once begun, the magazine never stopped; it is now being continued by his disciples in the West and is published in nineteen languages.</p>
        <p>Recognizing Śrīla Prabhupāda's philosophical learning and devotion, the Gauḍīya Vaiṣṇava Society honored him in 1947 with the title "Bhaktivedanta." In 1950, at the age of fifty-four, Śrīla Prabhupāda retired from married life, adopting the vānaprastha (retired) order to devote more time to his studies and writing. Śrīla Prabhupāda traveled to the holy city of Vṛndāvana, where he lived in very humble circumstances in the historic medieval temple of Rādhā-Dāmodara. There he engaged for several years in deep study and writing. He accepted the renounced order of life (sannyāsa) in 1959. At Rādhā-Dāmodara, Śrīla Prabhupāda began work on his life's masterpiece: a multivolume translation of and commentary on the eighteen-thousand-verse Śrīmad-Bhāgavatam (Bhāgavata Purāṇa). He also wrote Easy Journey to Other Planets.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container" style="max-width:900px;">
    <h2 style="text-align:center;margin-bottom:var(--space-2xl);">Journey to the West</h2>
    <div class="reveal" style="line-height:1.9;color:var(--text-light);">
      <p>After publishing three volumes of the Bhāgavatam, Śrīla Prabhupāda came to the United States, in 1965, to fulfill the mission of his spiritual master. Subsequently, His Divine Grace wrote more than sixty volumes of authoritative translations, commentaries and summary studies of the philosophical and religious classics of India.</p>
      <p>In 1965, when he first arrived by freighter in New York City, Śrīla Prabhupāda was practically penniless. It was after almost a year of great difficulty that he established the International Society for Krishna Consciousness in July of 1966. Before his passing away on November 14, 1977, he guided the Society and saw it grow to a worldwide confederation of more than one hundred āśramas, schools, temples, institutes and farm communities.</p>
      <p>In 1968, Śrīla Prabhupada created New Vrindaban, an experimental Vedic community in the hills of West Virginia. In 1972, His Divine Grace introduced the Vedic system of primary and secondary education in the West by founding the Gurukula school in Dallas, Texas.</p>
      <p>Śrīla Prabhupāda's most significant contribution, however, is his books. Highly respected by the academic community for their authoritativeness, depth and clarity, they are used as standard textbooks in numerous college courses. His writings have been translated into twenty-eight languages. The Bhaktivedanta Book Trust, established in 1972 exclusively to publish the works of His Divine Grace, has thus become the world's largest publisher of books in the field of Indian religion and philosophy.</p>
      <p>In just twelve years, in spite of his advanced age, Śrīla Prabhupāda circled the globe fourteen times on lecture tours that took him to six continents. In spite of such a vigorous schedule, Śrīla Prabhupāda continued to write prolifically. His writings constitute a veritable library of Vedic philosophy, religion, literature and culture.</p>
    </div>
  </div>
</section>

<!-- Chronology -->
<section class="section">
  <div class="container" style="max-width:900px;">
    <div style="text-align:center;margin-bottom:var(--space-3xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon">📅</span></div>
      <h2>Srila Prabhupada Chronology</h2>
    </div>

    <div style="display:grid;gap:var(--space-md);">
      <?php
      $chronology = [
        ['1896', 'Birth', 'Srila Prabhupada was born Abhay Charan De on 1st September 1896 in Kolkata, West Bengal, India, at about 4pm to father Gaura Mohan De and mother Rajani Devi.'],
        ['1918', 'Marriage', 'Enters married life with Radharani Datta.'],
        ['1922', 'Meets Spiritual Master', 'Meets for the first time Srila Bhaktisiddhanta Sarasvati Thakura – his future initiating spiritual master – who asks him to preach Lord Chaitanya\'s mission in the Western countries.'],
        ['1925', 'Visits Vrindavan', 'Visits Vrindavan (the holy land where Lord Krishna spent his childhood) for the first time and hears more from Srila Bhaktisiddhanta Sarasvati Gosvami.'],
        ['1933', 'Receives Initiation', 'Receives formal initiation from his spiritual master Srila Bhaktisiddhanta Sarasvati Gosvami.'],
        ['1935', 'Final Meeting', 'Travelled to Vrindavan to see his spiritual master, Srila Bhaktisiddhanta Sarasvati Gosvami, who gave him two essential instructions: print books and build temples.'],
        ['1944', 'Back to Godhead', 'Starts writing and publishing \'Back to Godhead\' magazine.'],
        ['1947', 'Title of Bhaktivedanta', 'Receives the honorary title of \'Bhaktivedanta\' from his God-brothers in the Gaudiya Math.'],
        ['1959', 'Sannyasa', 'Awarded sannyasa (the renounced order of life) in Mathura by one of his God-brothers, His Holiness B.P. Kesava Maharaj.'],
        ['1965', 'Voyage to USA', 'Voyage to the USA aboard the Jaladuta ship lasting one month, during which time he suffers two heart attacks.'],
        ['1965', 'Arrives in America', 'Historic arrival in America. The ship Jaladuta docks at Commonwealth Pier, Boston.'],
        ['1966', 'ISKCON Founded', 'Incorporates the International Society for Krishna Consciousness in New York City.'],
        ['1968', 'Bhagavad-gita Published', 'Bhagavad-gita As It Is abridged edition published by Macmillan.'],
        ['1970', 'GBC Established', 'Srila Prabhupada establishes the Governing Body Commission, the ultimate managing authority of ISKCON.'],
        ['1972', 'BBT Established', 'Establishes the Bhaktivedanta Book Trust.'],
        ['1974', 'Sri Chaitanya Charitamrita', 'Completes the translation of Sri Caitanya Caritamrita into English.'],
        ['1975', 'Vrindavan Temple', 'Opens Sri Sri Krishna Balarama Temple in Vrindavan – later to be his final resting place.'],
        ['1977', 'Departure', 'At the age of 81, Srila Prabhupada left the material world in Vrindavan, India.'],
      ];
      foreach ($chronology as $c):
      ?>
      <div class="reveal" style="display:flex;gap:var(--space-lg);align-items:flex-start;padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);border-left:3px solid var(--primary);">
        <div style="min-width:70px;text-align:center;">
          <span style="background:var(--gradient-primary);color:var(--white);padding:4px 12px;border-radius:var(--radius-sm);font-family:var(--font-heading);font-size:var(--font-size-sm);font-weight:600;white-space:nowrap;"><?php echo $c[0]; ?></span>
        </div>
        <div>
          <strong style="color:var(--dark);"><?php echo $c[1]; ?></strong>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;margin:2px 0 0;"><?php echo $c[2]; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Signature -->
<section class="section section-alt" style="text-align:center;">
  <div class="container">
    <div class="reveal">
      <img src="https://iskconbangalore.co.in/sites/default/files/styles/tv_1x_1400/public/2023-07/Prabhupada_sig.jpg?itok=uhOHlaSy" alt="Srila Prabhupada Signature" style="max-width:300px;margin:0 auto;opacity:0.7;">
      <p style="margin-top:var(--space-md);color:var(--text-light);font-size:var(--font-size-sm);font-style:italic;">— His Divine Grace A.C. Bhaktivedanta Swami Prabhupada</p>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
