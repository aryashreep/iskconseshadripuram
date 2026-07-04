<?php
$pageTitle = 'Festivals at ISKCON Seshadripuram';
$metaDescription = 'Explore Vaishnava festivals at ISKCON The Palace Temple of Lord Jagannath in Bangalore. Janmashtami, Rath Yatra, Ekadashi dates, appearance days & grand celebrations.';
$pageType = 'festival';
include '../partials/header.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('../assets/images/banners/calendar.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Vaishnava Festivals</h1>
    <div class="breadcrumb reveal">
      <a href="/isjm/">Home</a><span>›</span><span>Festivals</span>
    </div>
  </div>
</section>

<!-- Page Content -->
<section class="page-content">
  <div class="container" style="max-width: 1000px;">
    
    <!-- Title & Intro -->
    <div class="reveal" style="text-align: center; margin-bottom: var(--space-3xl);">
      <div class="section-divider"><span class="divider-icon">🎉</span></div>
      <h2>Celebrations & Sacred Observances</h2>
      <p style="color: var(--text-light); max-width: 800px; margin: 0 auto; line-height: 1.8; font-size: var(--font-size-base);">
        Festivals are the life and soul of spiritual practice in Krishna consciousness. At ISKCON Seshadripuram, we celebrate the appearances of incarnations, anniversaries of acharyas, and holy observances with joyous kirtan, scriptural discussions, and sumptuous prasadam.
      </p>
    </div>

    <!-- Category Portal Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-xl); margin-bottom: var(--space-3xl);">
      
      <!-- Grand Festivals -->
      <div class="reveal" style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; transition: all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
        <div style="height: 140px; background-image: url('../assets/images/banners/banner1.jpg'); background-size: cover; background-position: center; position: relative;">
          <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3);"></div>
        </div>
        <div style="padding: var(--space-lg); flex-grow: 1; display: flex; flex-direction: column;">
          <h3 style="margin-top: 0; margin-bottom: var(--space-sm); color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 20px;">🚩</span> Grand Festivals
          </h3>
          <p style="color: var(--text-light); font-size: var(--font-size-sm); line-height: 1.6; flex-grow: 1; margin: 0 0 var(--space-md) 0;">
            Discover our major celebrations throughout the year including Janmashtami, Rath Yatra, Gaura Purnima, and Diwali.
          </p>
          <a href="festivals/grand-festivals/" class="btn btn-primary btn-sm" style="align-self: flex-start; text-decoration: none;">Explore Festivals</a>
        </div>
      </div>

      <!-- Ekadashi -->
      <div class="reveal" style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; transition: all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
        <div style="height: 140px; background-image: url('../assets/images/banners/banner2.jpg'); background-size: cover; background-position: center; position: relative;">
          <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3);"></div>
        </div>
        <div style="padding: var(--space-lg); flex-grow: 1; display: flex; flex-direction: column;">
          <h3 style="margin-top: 0; margin-bottom: var(--space-sm); color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 20px;">🌙</span> Ekadashi Fasting
          </h3>
          <p style="color: var(--text-light); font-size: var(--font-size-sm); line-height: 1.6; flex-grow: 1; margin: 0 0 var(--space-md) 0;">
            Access the dates and specific names for all Ekadashis in 2026, alongside dietary rules and fasting guidelines.
          </p>
          <a href="festivals/ekadashi/" class="btn btn-primary btn-sm" style="align-self: flex-start; text-decoration: none;">View Ekadashis</a>
        </div>
      </div>

      <!-- Appearance Days -->
      <div class="reveal" style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; transition: all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
        <div style="height: 140px; background-image: url('../assets/images/banners/banner3.jpg'); background-size: cover; background-position: center; position: relative;">
          <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3);"></div>
        </div>
        <div style="padding: var(--space-lg); flex-grow: 1; display: flex; flex-direction: column;">
          <h3 style="margin-top: 0; margin-bottom: var(--space-sm); color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 20px;">🪷</span> Appearance Days
          </h3>
          <p style="color: var(--text-light); font-size: var(--font-size-sm); line-height: 1.6; flex-grow: 1; margin: 0 0 var(--space-md) 0;">
            Learn about dates and observances commemorating the descent of Lord Krishna, His expansions, and preeminent acharyas.
          </p>
          <a href="festivals/appearance/" class="btn btn-primary btn-sm" style="align-self: flex-start; text-decoration: none;">See Appearance Days</a>
        </div>
      </div>

      <!-- Disappearance Days -->
      <div class="reveal" style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; transition: all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
        <div style="height: 140px; background-image: url('../assets/images/banners/banner4.jpg'); background-size: cover; background-position: center; position: relative;">
          <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3);"></div>
        </div>
        <div style="padding: var(--space-lg); flex-grow: 1; display: flex; flex-direction: column;">
          <h3 style="margin-top: 0; margin-bottom: var(--space-sm); color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 20px;">🌟</span> Disappearance Days
          </h3>
          <p style="color: var(--text-light); font-size: var(--font-size-sm); line-height: 1.6; flex-grow: 1; margin: 0 0 var(--space-md) 0;">
            Honor the departure anniversaries of the Vaishnava acharyas who paved the way for Krishna consciousness.
          </p>
          <a href="festivals/disappearance/" class="btn btn-primary btn-sm" style="align-self: flex-start; text-decoration: none;">View Departures</a>
        </div>
      </div>

      <!-- Events -->
      <div class="reveal" style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; transition: all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
        <div style="height: 140px; background-image: url('../assets/images/banners/banner5.jpg'); background-size: cover; background-position: center; position: relative;">
          <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3);"></div>
        </div>
        <div style="padding: var(--space-lg); flex-grow: 1; display: flex; flex-direction: column;">
          <h3 style="margin-top: 0; margin-bottom: var(--space-sm); color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 20px;">📜</span> Special Events
          </h3>
          <p style="color: var(--text-light); font-size: var(--font-size-sm); line-height: 1.6; flex-grow: 1; margin: 0 0 var(--space-md) 0;">
            Read details about periodic vows and seasonal observances such as Jhulana Yatra, Caturmasya, and Bhisma Panchaka.
          </p>
          <a href="festivals/events/" class="btn btn-primary btn-sm" style="align-self: flex-start; text-decoration: none;">Learn About Events</a>
        </div>
      </div>

      <!-- Calendar -->
      <div class="reveal" style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; transition: all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
        <div style="height: 140px; background-image: url('../assets/images/banners/banner6.jpg'); background-size: cover; background-position: center; position: relative;">
          <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3);"></div>
        </div>
        <div style="padding: var(--space-lg); flex-grow: 1; display: flex; flex-direction: column;">
          <h3 style="margin-top: 0; margin-bottom: var(--space-sm); color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 20px;">📅</span> 2026 Calendar Sheet
          </h3>
          <p style="color: var(--text-light); font-size: var(--font-size-sm); line-height: 1.6; flex-grow: 1; margin: 0 0 var(--space-md) 0;">
            Browse the calendar by month, download the official poster grids, and view exact fasting days.
          </p>
          <a href="festivals/vaishnava-calendar/" class="btn btn-primary btn-sm" style="align-self: flex-start; text-decoration: none;">Go to Calendar</a>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
