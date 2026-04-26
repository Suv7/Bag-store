<?php
session_start();
include("config.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carry — Bags For Life</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    :root {
        --plum:       #6B2D6B;
        --plum-mid:   #902c7e;
        --plum-pale:  #f5e8f3;
        --gold:       #C9A96E;
        --gold-light: #e8d5b0;
        --cream:      #FAF8F5;
        --warm-white: #FFFDF9;
        --charcoal:   #1A1A1A;
        --gray:       #888;
    }
    html { scroll-behavior: smooth; }
    body { font-family:'DM Sans',sans-serif; background:var(--cream); color:var(--charcoal); overflow-x:hidden; }

    /* NAV */
    .nav {
        position:sticky; top:0; z-index:200;
        background:var(--plum);
        display:flex; align-items:center; justify-content:space-between;
        padding:0 4rem; height:68px;
        box-shadow:0 4px 30px rgba(107,45,107,.4);
    }
    .nav-logo { font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:600; color:#fff; letter-spacing:.08em; text-decoration:none; }
    .nav-logo span { color:var(--gold); }
    .nav-links { display:flex; align-items:center; gap:2.5rem; list-style:none; }
    .nav-links a { color:rgba(255,255,255,.8); text-decoration:none; font-size:.8rem; font-weight:400; letter-spacing:.12em; text-transform:uppercase; transition:color .2s; position:relative; }
    .nav-links a::after { content:''; position:absolute; bottom:-4px; left:0; right:0; height:1px; background:var(--gold); transform:scaleX(0); transition:transform .25s; }
    .nav-links a:hover { color:#fff; }
    .nav-links a:hover::after { transform:scaleX(1); }
    .nav-logout { color:var(--gold-light) !important; }
    .nav-cart { color:#fff; font-size:1.1rem; text-decoration:none; transition:color .2s; }
    .nav-cart:hover { color:var(--gold); }

    /* HERO */
    .hero {
        min-height:calc(100vh - 68px);
        display:grid; grid-template-columns:55% 45%;
        background:linear-gradient(150deg, var(--cream) 55%, var(--plum-pale) 100%);
        position:relative; overflow:hidden;
    }
    .hero::before { content:''; position:absolute; top:-200px; right:0; width:700px; height:700px; border-radius:50%; background:radial-gradient(circle,rgba(144,44,126,.08) 0%,transparent 65%); pointer-events:none; }
    .hero::after  { content:''; position:absolute; bottom:-100px; left:300px; width:400px; height:400px; border-radius:50%; background:radial-gradient(circle,rgba(201,169,110,.07) 0%,transparent 70%); pointer-events:none; }

    .hero-left { display:flex; flex-direction:column; justify-content:center; padding:5rem 3rem 5rem 6rem; position:relative; z-index:1; animation:fadeUp .9s ease both; }
    .hero-tag { display:inline-flex; align-items:center; gap:.6rem; font-size:.7rem; letter-spacing:.2em; text-transform:uppercase; font-weight:500; color:var(--gold); margin-bottom:1.5rem; }
    .hero-tag::before { content:''; display:inline-block; width:28px; height:1px; background:var(--gold); }
    .hero-h1 { font-family:'Cormorant Garamond',serif; font-size:clamp(3.8rem,5.5vw,6.5rem); font-weight:300; line-height:1.02; color:var(--charcoal); margin-bottom:1.8rem; }
    .hero-h1 em { font-style:italic; font-weight:400; color:var(--plum-mid); }
    .hero-sub { font-size:1rem; color:#666; line-height:1.75; max-width:400px; margin-bottom:3rem; }
    .hero-actions { display:flex; align-items:center; gap:1.5rem; }
    .btn-primary { display:inline-flex; align-items:center; gap:.75rem; background:var(--plum); color:#fff; text-decoration:none; padding:1rem 2.4rem; font-size:.8rem; letter-spacing:.1em; text-transform:uppercase; font-weight:500; border-radius:2px; transition:background .25s,transform .2s,box-shadow .25s; box-shadow:0 4px 20px rgba(107,45,107,.3); }
    .btn-primary:hover { background:var(--plum-mid); transform:translateY(-2px); box-shadow:0 8px 30px rgba(107,45,107,.4); }
    .btn-ghost { display:inline-flex; align-items:center; gap:.5rem; color:var(--charcoal); text-decoration:none; font-size:.8rem; letter-spacing:.1em; text-transform:uppercase; font-weight:500; border-bottom:1px solid currentColor; padding-bottom:2px; transition:color .2s; }
    .btn-ghost:hover { color:var(--plum-mid); }

    .hero-right { position:relative; overflow:hidden; animation:fadeIn 1.1s .2s ease both; }
    .hero-img-frame { position:absolute; inset:0; display:flex; align-items:flex-end; justify-content:center; }
    .hero-img-frame img { height:92%; width:auto; object-fit:contain; object-position:bottom; filter:drop-shadow(0 30px 60px rgba(107,45,107,.2)); transition:transform .5s ease; }
    .hero-img-frame img:hover { transform:scale(1.03) translateY(-6px); }

    .hero-pill { position:absolute; bottom:3rem; left:2rem; z-index:2; background:var(--charcoal); color:#fff; display:flex; align-items:center; gap:.75rem; padding:.75rem 1.4rem; font-size:.7rem; letter-spacing:.12em; text-transform:uppercase; border-left:3px solid var(--gold); animation:slideRight .8s .5s ease both; box-shadow:0 8px 30px rgba(0,0,0,.25); }
    .hero-pill i { color:var(--gold); }
    .hero-stat { position:absolute; top:3rem; right:2rem; z-index:2; background:rgba(255,255,255,.92); backdrop-filter:blur(10px); border:1px solid rgba(201,169,110,.3); padding:1rem 1.5rem; text-align:center; animation:slideLeft .8s .6s ease both; box-shadow:0 8px 30px rgba(0,0,0,.08); }
    .hero-stat-num { font-family:'Cormorant Garamond',serif; font-size:2.2rem; font-weight:600; color:var(--plum); line-height:1; }
    .hero-stat-label { font-size:.65rem; letter-spacing:.15em; text-transform:uppercase; color:var(--gray); margin-top:.25rem; }

    /* FEATURES BAR */
    .features-bar { background:var(--plum); display:grid; grid-template-columns:repeat(3,1fr); }
    .feat { display:flex; align-items:center; gap:1rem; padding:1.75rem 3rem; border-right:1px solid rgba(255,255,255,.12); color:#fff; }
    .feat:last-child { border-right:none; }
    .feat-icon { width:40px; height:40px; background:rgba(255,255,255,.12); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.9rem; color:var(--gold); flex-shrink:0; }
    .feat-t { font-size:.8rem; font-weight:500; letter-spacing:.05em; }
    .feat-s { font-size:.7rem; color:rgba(255,255,255,.55); margin-top:.15rem; }

    /* SECTION HEADER */
    .sec-head { text-align:center; padding:5.5rem 2rem 3.5rem; }
    .sec-eye { font-size:.68rem; letter-spacing:.25em; text-transform:uppercase; color:var(--gold); font-weight:500; margin-bottom:.8rem; }
    .sec-ttl { font-family:'Cormorant Garamond',serif; font-size:clamp(2rem,3.5vw,3rem); font-weight:400; color:var(--charcoal); line-height:1.2; }
    .sec-rule { width:40px; height:1px; background:var(--gold); margin:1.2rem auto 0; }

    /* PRODUCT GRID */
    .products-wrap { padding:0 5rem 6rem; max-width:1400px; margin:0 auto; }
    .products-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); gap:1.75rem; }
    .p-card { background:var(--warm-white); border:1px solid rgba(0,0,0,.05); text-decoration:none; color:inherit; display:flex; flex-direction:column; position:relative; overflow:hidden; transition:transform .35s ease,box-shadow .35s ease; border-radius:3px; }
    .p-card:hover { transform:translateY(-8px); box-shadow:0 24px 60px rgba(107,45,107,.13); }
    .p-img { aspect-ratio:1/1; overflow:hidden; background:var(--plum-pale); position:relative; }
    .p-img img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
    .p-card:hover .p-img img { transform:scale(1.08); }
    .p-overlay { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(107,45,107,0); transition:background .35s; }
    .p-card:hover .p-overlay { background:rgba(107,45,107,.06); }
    .p-quick { background:var(--plum); color:#fff; padding:.65rem 1.4rem; font-size:.72rem; letter-spacing:.1em; text-transform:uppercase; opacity:0; transform:translateY(10px); transition:opacity .3s,transform .3s; }
    .p-card:hover .p-quick { opacity:1; transform:translateY(0); }
    .p-body { padding:1.2rem 1.4rem 1.6rem; }
    .p-name { font-family:'Cormorant Garamond',serif; font-size:1.15rem; font-weight:600; color:var(--charcoal); line-height:1.3; margin-bottom:.35rem; }
    .p-price { font-size:.85rem; color:var(--plum-mid); font-weight:500; }

    /* ABOUT */
    .about { display:grid; grid-template-columns:1fr 1fr; min-height:440px; margin-top:2rem; }
    .about-img { background:var(--plum) url('ok-removebg-preview.png') center/contain no-repeat; position:relative; min-height:440px; }
    .about-img::after { content:''; position:absolute; inset:0; background:linear-gradient(to right,transparent 55%,var(--plum)); }
    .about-body { background:var(--plum); color:#fff; display:flex; flex-direction:column; justify-content:center; padding:5rem 5rem 5rem 4rem; gap:1.5rem; }
    .about-eye { font-size:.68rem; letter-spacing:.25em; text-transform:uppercase; color:var(--gold); }
    .about-ttl { font-family:'Cormorant Garamond',serif; font-size:2.8rem; font-weight:300; line-height:1.15; }
    .about-txt { font-size:.9rem; line-height:1.85; color:rgba(255,255,255,.7); max-width:380px; }
    .about-lnk { display:inline-flex; align-items:center; gap:.6rem; color:var(--gold); text-decoration:none; font-size:.78rem; letter-spacing:.1em; text-transform:uppercase; border-bottom:1px solid rgba(201,169,110,.4); padding-bottom:3px; width:fit-content; transition:border-color .2s; }
    .about-lnk:hover { border-color:var(--gold); }

    /* FOOTER */
    footer { background:var(--charcoal); padding:2.5rem 4rem; display:flex; align-items:center; justify-content:space-between; }
    .f-brand { font-family:'Cormorant Garamond',serif; font-size:1.4rem; color:#fff; font-weight:600; letter-spacing:.1em; }
    .f-brand span { color:var(--gold); }
    .f-copy { font-size:.75rem; color:rgba(255,255,255,.4); letter-spacing:.04em; }
    .f-social { display:flex; gap:1rem; }
    .f-social a { width:34px; height:34px; border:1px solid rgba(255,255,255,.15); border-radius:50%; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.5); font-size:.8rem; text-decoration:none; transition:border-color .2s,color .2s; }
    .f-social a:hover { border-color:var(--gold); color:var(--gold); }

    /* ANIMATIONS */
    @keyframes fadeUp   { from{opacity:0;transform:translateY(32px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fadeIn   { from{opacity:0} to{opacity:1} }
    @keyframes slideRight { from{opacity:0;transform:translateX(-24px)} to{opacity:1;transform:translateX(0)} }
    @keyframes slideLeft  { from{opacity:0;transform:translateX(24px)}  to{opacity:1;transform:translateX(0)} }

    .reveal { opacity:0; transform:translateY(24px); transition:opacity .7s ease,transform .7s ease; }
    .reveal.visible { opacity:1; transform:translateY(0); }

    /* RESPONSIVE */
    @media(max-width:1024px){
        .hero{grid-template-columns:1fr; min-height:auto;}
        .hero-right{min-height:420px;}
        .hero-left{padding:4rem 2rem; text-align:center;}
        .hero-sub{margin:0 auto 2.5rem;}
        .hero-actions{justify-content:center;}
        .about{grid-template-columns:1fr;}
        .about-img{min-height:300px;}
        .features-bar{grid-template-columns:1fr;}
        .feat{border-right:none; border-bottom:1px solid rgba(255,255,255,.12);}
        .products-wrap{padding:0 1.5rem 4rem;}
        .nav{padding:0 1.5rem;}
        footer{flex-direction:column; gap:1rem; text-align:center;}
    }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
    <a href="index.php" class="nav-logo">Carry<span>.</span></a>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="pages/shop.php">Shop</a></li>
        <li><a href="#about">About</a></li>
        <?php if (isset($_SESSION["uid"])): ?>
            <li><a href="logout.php" class="nav-logout">Log out</a></li>
            <li><a href="cart.php" class="nav-cart"><i class="fas fa-shopping-bag"></i></a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-left">
        <span class="hero-tag">New Collection 2025</span>
        <h1 class="hero-h1">Bags crafted<br>for <em>real life</em></h1>
        <p class="hero-sub">Minimalist designs. Premium materials. Every Carry bag is made to move with you — from morning commute to evening out.</p>
        <div class="hero-actions">
            <a href="pages/shop.php" class="btn-primary">Shop Collection <i class="fas fa-arrow-right" style="font-size:.7rem"></i></a>
            <a href="#about" class="btn-ghost">Our Story <i class="fas fa-arrow-right" style="font-size:.65rem"></i></a>
        </div>
    </div>
    <div class="hero-right">
        <div class="hero-img-frame">
            <img src="ok-removebg-preview.png" alt="Carry Signature Bag">
        </div>
        <div class="hero-pill"><i class="fas fa-truck"></i> Free delivery in Kathmandu</div>
        <div class="hero-stat">
            <div class="hero-stat-num">500+</div>
            <div class="hero-stat-label">Happy Customers</div>
        </div>
    </div>
</section>

<!-- FEATURES BAR -->
<div class="features-bar">
    <div class="feat"><div class="feat-icon"><i class="fas fa-truck"></i></div><div><div class="feat-t">Free Delivery</div><div class="feat-s">Across Kathmandu valley</div></div></div>
    <div class="feat"><div class="feat-icon"><i class="fas fa-shield-alt"></i></div><div><div class="feat-t">Quality Guaranteed</div><div class="feat-s">Crafted to global standards</div></div></div>
    <div class="feat"><div class="feat-icon"><i class="fas fa-undo"></i></div><div><div class="feat-t">Easy Returns</div><div class="feat-s">No questions asked policy</div></div></div>
</div>

<!-- PRODUCTS -->
<section>
    <div class="sec-head reveal">
        <p class="sec-eye">Just Arrived</p>
        <h2 class="sec-ttl">Recently Added Bags</h2>
        <div class="sec-rule"></div>
    </div>
    <div class="products-wrap">
        <div class="products-grid">
            <?php
            $sql = "SELECT * FROM products ps JOIN product_name_price pnp ON ps.pnp = pnp.id ORDER BY createdDate DESC LIMIT 10";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)):
            ?>
            <a class="p-card reveal" href="BagInfo.php?id=<?= (int)$row['id'] ?>">
                <div class="p-img">
                    <img src="<?= getImageUrl($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <div class="p-overlay"><span class="p-quick">View Details</span></div>
                </div>
                <div class="p-body">
                    <div class="p-name"><?= htmlspecialchars($row['name']) ?></div>
                    <div class="p-price">Rs. <?= number_format($row['price'], 2) ?></div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section class="about" id="about">
    <div class="about-img"></div>
    <div class="about-body reveal">
        <p class="about-eye">Our Story</p>
        <h2 class="about-ttl">Proudly made<br>in Nepal</h2>
        <p class="about-txt">Carry was born in Kathmandu with one belief — quality should never be a compromise. Every design is crafted locally, built to global standards, and made to last a lifetime.</p>
        <a href="pages/shop.php" class="about-lnk">Explore Collection <i class="fas fa-arrow-right" style="font-size:.65rem"></i></a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="f-brand">Carry<span>.</span></div>
    <div class="f-copy">&copy; 2025 Carry &mdash; All Rights Reserved. Kathmandu, Nepal.</div>
    <div class="f-social">
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-tiktok"></i></a>
    </div>
</footer>

<script>
const obs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            setTimeout(() => e.target.classList.add('visible'), i * 80);
            obs.unobserve(e.target);
        }
    });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
</script>
</body>
</html>