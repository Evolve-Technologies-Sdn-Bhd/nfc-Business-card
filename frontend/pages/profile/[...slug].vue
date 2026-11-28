<template>
  <div
    :style="{
      minHeight: '100vh',
      width: '100%',
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      background: designSettings.bgGradient || designSettings.backgroundColor,
      color: designSettings.textColor,
      position: 'relative',
      overflow: 'hidden',
      overflowX: 'hidden',
      fontFamily: designSettings.fontFamily,
      '--accent': designSettings.accentColor,
      boxSizing: 'border-box',
    }"
  >
    <!-- Animations & Global Styles -->
    <component :is="'style'">
      {{
        `
        @keyframes float { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(30px, -30px) scale(1.05); } }
        @keyframes floatSlow { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-20px, 20px); } }
        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulse-status { 0%, 100% { box-shadow: 0 0 0 0 rgba(194, 160, 104, 0.7); } 50% { box-shadow: 0 0 0 10px rgba(194, 160, 104, 0); } }
        @keyframes charBounce { 0% { opacity: 0; transform: translateY(20px); } 50% { transform: translateY(-10px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes particleFloat { 0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; } 50% { transform: translateY(-20px) scale(1.2); opacity: 0.6; } }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes rippleEffect { to { transform: scale(2); opacity: 0; } }
        @keyframes logoRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes auroraShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes twinkle { 0%, 100% { opacity: 0.2; transform: scale(0.8); } 50% { opacity: 1; transform: scale(1.15); } }
        @keyframes drift { 0% { transform: translate(-5%, -5%); } 100% { transform: translate(5%, 5%); } }
        @keyframes shimmer { 0%, 100% { opacity: 0; transform: rotate(0deg) scale(0.5); } 50% { opacity: 0.95; transform: rotate(180deg) scale(1); } }
        @keyframes waveShift { 0% { background-position: 0 0; } 100% { background-position: 200px 0; } }
        @keyframes meshMove { 0%, 100% { transform: translate(0, 0) scale(1); } 33% { transform: translate(5%, -6%) scale(1.08); } 66% { transform: translate(-4%, 4%) scale(0.97); } }
        .card-section { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-section:hover { transform: translateY(-4px); }

        /* Edge-light glow — a warm brass rim that lets the panels catch light
           rather than float flat on the ink. Kept low-alpha so it reads as
           premium ambience, never neon. */
        .profile-glow,
        .section-grid > div {
          transition: box-shadow 0.45s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.45s ease;
        }
        .profile-glow:hover,
        .section-grid > div:hover {
          border-color: rgba(194, 160, 104, 0.34) !important;
          box-shadow:
            0 36px 72px -34px rgba(0, 0, 0, 0.98),
            0 0 0 1px rgba(194, 160, 104, 0.24),
            0 0 64px -14px rgba(194, 160, 104, 0.42),
            inset 0 1px 0 rgba(194, 160, 104, 0.3) !important;
        }
        /* Section header icon chips carry a faint warm bloom at rest */
        .section-grid > div > div:first-child > div:first-child {
          box-shadow: 0 0 20px -7px rgba(194, 160, 104, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.07) !important;
        }
        /* Quick-action chips answer the cursor with a soft rim light */
        .quick-actions > button:hover {
          border-color: rgba(194, 160, 104, 0.55) !important;
          box-shadow: 0 0 26px -8px rgba(194, 160, 104, 0.5) !important;
        }

        :root {
          --sat: env(safe-area-inset-top, 0px);
          --sab: env(safe-area-inset-bottom, 0px);
          --sal: env(safe-area-inset-left, 0px);
          --sar: env(safe-area-inset-right, 0px);
        }

        html, body, #__nuxt, #__layout {
          -webkit-text-size-adjust: 100%;
          text-size-adjust: 100%;
          -webkit-tap-highlight-color: transparent;
          overflow-x: hidden;
        }

        * {
          -webkit-tap-highlight-color: transparent;
        }

        button, a {
          -webkit-touch-callout: none;
        }

        .fade-slide-enter-active, .fade-slide-leave-active {
          transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .fade-slide-enter-from {
          opacity: 0;
          transform: translateX(-20px);
        }
        .fade-slide-leave-to {
          opacity: 0;
          transform: translateX(-20px);
        }

        @media (max-width: 374px) {
          .profile-page__orbs {
            display: none;
          }
        }
      `
      }}
    </component>

    <!-- ============== DYNAMIC BACKGROUND ANIMATION LAYER ============== -->
    <div
      v-if="designSettings.bgAnimation !== 'none'"
      :style="{
        position: 'fixed',
        top: 0, left: 0, width: '100%', height: '100%',
        zIndex: 0, pointerEvents: 'none', overflow: 'hidden',
      }"
    >
      <!-- Floating Particles (Crystal Clear Bokeh) -->
      <template v-if="designSettings.bgAnimation === 'particles'">
        <div
          v-for="i in 46"
          :key="'p'+i"
          :style="{
            position: 'absolute',
            width: (i % 4 === 0 ? '14px' : i % 3 === 0 ? '10px' : i % 2 === 0 ? '7px' : '5px'),
            height: (i % 4 === 0 ? '14px' : i % 3 === 0 ? '10px' : i % 2 === 0 ? '7px' : '5px'),
            borderRadius: '9999px',
            background: i % 3 === 0
              ? `radial-gradient(circle at 30% 30%, ${designSettings.accentColor}, ${designSettings.textColor})`
              : designSettings.textColor,
            opacity: i % 4 === 0 ? 0.78 : i % 3 === 0 ? 0.55 : i % 2 === 0 ? 0.38 : 0.26,
            top: ((i * 137) % 100) + '%',
            left: ((i * 89) % 100) + '%',
            animation: `particleFloat ${5 + (i % 7)}s ease-in-out infinite`,
            animationDelay: ((i * 0.21) % 4) + 's',
            boxShadow: i % 4 === 0
              ? `0 0 14px ${designSettings.accentColor}aa, 0 0 6px ${designSettings.textColor}88`
              : `0 0 7px ${designSettings.textColor}66`,
          }"
        ></div>
      </template>

      <!-- Aurora Borealis (Brighter + Multicolor Bands) -->
      <template v-else-if="designSettings.bgAnimation === 'aurora'">
        <div
          :style="{
            position: 'absolute', inset: '-25%',
            background: `linear-gradient(115deg, rgba(169, 133, 77,0.72), rgba(169, 133, 77,0.68) 22%, rgba(138, 106, 51,0.6) 45%, rgba(138, 106, 51,0.62) 68%, rgba(169, 133, 77,0.7) 90%)`,
            backgroundSize: '300% 300%',
            animation: 'auroraShift 14s ease infinite',
            filter: 'blur(52px)',
            mixBlendMode: 'screen',
          }"
        ></div>
        <div
          :style="{
            position: 'absolute', inset: '-15%',
            background: `radial-gradient(ellipse at 20% 20%, ${designSettings.accentColor}55 0%, transparent 55%), radial-gradient(ellipse at 80% 70%, rgba(138, 106, 51,0.45) 0%, transparent 50%)`,
            backgroundSize: '220% 220%',
            animation: 'auroraShift 22s ease-in-out infinite reverse',
            filter: 'blur(35px)',
          }"
        ></div>
      </template>

      <!-- Gradient Mesh Blobs (Thicker + Sharper Edges) -->
      <template v-else-if="designSettings.bgAnimation === 'mesh-gradient'">
        <div
          :style="{
            position: 'absolute',
            width: '62%', height: '62%',
            top: '-18%', left: '-14%',
            background: 'radial-gradient(circle, rgba(194, 160, 104,0.82) 0%, rgba(194, 160, 104,0.15) 45%, transparent 72%)',
            animation: 'meshMove 18s ease-in-out infinite',
            filter: 'blur(22px)',
            mixBlendMode: 'screen',
          }"
        ></div>
        <div
          :style="{
            position: 'absolute',
            width: '58%', height: '58%',
            top: '12%', right: '-18%',
            background: 'radial-gradient(circle, rgba(138, 106, 51,0.78) 0%, rgba(138, 106, 51,0.12) 45%, transparent 70%)',
            animation: 'meshMove 22s ease-in-out infinite reverse',
            filter: 'blur(22px)',
            mixBlendMode: 'screen',
          }"
        ></div>
        <div
          :style="{
            position: 'absolute',
            width: '68%', height: '60%',
            bottom: '-22%', left: '22%',
            background: 'radial-gradient(circle, rgba(138, 106, 51,0.78) 0%, rgba(138, 106, 51,0.15) 45%, transparent 72%)',
            animation: 'meshMove 20s ease-in-out infinite',
            animationDelay: '-4s',
            filter: 'blur(22px)',
            mixBlendMode: 'screen',
          }"
        ></div>
        <div
          :style="{
            position: 'absolute',
            width: '48%', height: '48%',
            top: '30%', left: '26%',
            background: `radial-gradient(circle, ${designSettings.accentColor}88 0%, transparent 60%)`,
            animation: 'meshMove 26s ease-in-out infinite reverse',
            animationDelay: '-8s',
            filter: 'blur(28px)',
            mixBlendMode: 'screen',
          }"
        ></div>
      </template>

      <!-- Starfield (Crisp White Stars w/ Distinct Sizes) -->
      <template v-else-if="designSettings.bgAnimation === 'starfield'">
        <template v-for="i in 90" :key="'s'+i">
          <div
            v-if="i % 7 === 0"
            :style="{
              position: 'absolute',
              width: '3px', height: '3px',
              borderRadius: '9999px',
              background: '#FFFFFF',
              top: ((i * 149) % 100) + '%',
              left: ((i * 71) % 100) + '%',
              animation: `twinkle ${2 + (i % 4)}s ease-in-out infinite`,
              animationDelay: ((i * 0.13) % 3) + 's',
              boxShadow: '0 0 10px rgba(255,255,255,0.95), 0 0 20px rgba(232, 217, 184,0.6)',
            }"
          ></div>
          <div
            v-else-if="i % 3 === 0"
            :style="{
              position: 'absolute',
              width: '2px', height: '2px',
              borderRadius: '9999px',
              background: '#FFFFFF',
              top: ((i * 127) % 100) + '%',
              left: ((i * 89) % 100) + '%',
              animation: `twinkle ${2.5 + (i % 5)}s ease-in-out infinite`,
              animationDelay: ((i * 0.17) % 3) + 's',
              boxShadow: '0 0 6px rgba(255,255,255,0.85)',
            }"
          ></div>
          <div
            v-else
            :style="{
              position: 'absolute',
              width: '1.3px', height: '1.3px',
              borderRadius: '9999px',
              background: '#FFFFFF',
              top: ((i * 181) % 100) + '%',
              left: ((i * 101) % 100) + '%',
              animation: `twinkle ${3 + (i % 5)}s ease-in-out infinite`,
              animationDelay: ((i * 0.11) % 3) + 's',
              boxShadow: '0 0 3px rgba(255,255,255,0.7)',
            }"
          ></div>
        </template>
      </template>

      <!-- Slow Waves (Deeper Path + 3 Layers + Stronger Opacity) -->
      <template v-else-if="designSettings.bgAnimation === 'slow-waves'">
        <svg :style="{ position: 'absolute', inset: 0, width: '100%', height: '100%' }" preserveAspectRatio="none" viewBox="0 0 1200 800">
          <defs>
            <linearGradient id="wave1" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" :stop-color="designSettings.accentColor" stop-opacity="0.38" />
              <stop offset="60%" :stop-color="designSettings.textColor" stop-opacity="0.14" />
              <stop offset="100%" :stop-color="designSettings.textColor" stop-opacity="0" />
            </linearGradient>
            <linearGradient id="wave2" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" :stop-color="designSettings.textColor" stop-opacity="0.22" />
              <stop offset="100%" :stop-color="designSettings.textColor" stop-opacity="0" />
            </linearGradient>
          </defs>
          <path d="M0,120 C200,20 400,220 600,140 C800,60 1000,240 1200,160 L1200,800 L0,800 Z" fill="url(#wave1)">
            <animate attributeName="d" values="
              M0,120 C200,20 400,220 600,140 C800,60 1000,240 1200,160 L1200,800 L0,800 Z;
              M0,170 C200,280 400,80 600,190 C800,280 1000,80 1200,180 L1200,800 L0,800 Z;
              M0,120 C200,20 400,220 600,140 C800,60 1000,240 1200,160 L1200,800 L0,800 Z
            " dur="11s" repeatCount="indefinite" />
          </path>
          <path d="M0,300 C200,200 400,400 600,300 C800,200 1000,400 1200,320 L1200,800 L0,800 Z" fill="url(#wave2)">
            <animate attributeName="d" values="
              M0,300 C200,200 400,400 600,300 C800,200 1000,400 1200,320 L1200,800 L0,800 Z;
              M0,360 C200,450 400,260 600,360 C800,460 1000,260 1200,380 L1200,800 L0,800 Z;
              M0,300 C200,200 400,400 600,300 C800,200 1000,400 1200,320 L1200,800 L0,800 Z
            " dur="14s" repeatCount="indefinite" />
          </path>
          <path d="M0,520 C200,440 400,600 600,520 C800,440 1000,600 1200,540 L1200,800 L0,800 Z" fill="url(#wave2)" opacity="0.55">
            <animate attributeName="d" values="
              M0,520 C200,440 400,600 600,520 C800,440 1000,600 1200,540 L1200,800 L0,800 Z;
              M0,580 C200,660 400,500 600,590 C800,670 1000,500 1200,610 L1200,800 L0,800 Z;
              M0,520 C200,440 400,600 600,520 C800,440 1000,600 1200,540 L1200,800 L0,800 Z
            " dur="17s" repeatCount="indefinite" />
          </path>
        </svg>
      </template>

      <!-- Magic Sparkle (Bigger + More + Accent Glow) -->
      <template v-else-if="designSettings.bgAnimation === 'sparkle'">
        <template v-for="i in 34" :key="'sp'+i">
          <div
            :style="{
              position: 'absolute',
              width: (i % 4 === 0 ? '40px' : i % 3 === 0 ? '30px' : '22px'),
              height: (i % 4 === 0 ? '40px' : i % 3 === 0 ? '30px' : '22px'),
              top: ((i * 151) % 100) + '%',
              left: ((i * 97) % 100) + '%',
              animation: `shimmer ${2.6 + (i % 5)}s ease-in-out infinite`,
              animationDelay: ((i * 0.29) % 3.5) + 's',
            }"
          >
            <svg viewBox="0 0 24 24" fill="none" :style="{
              filter: `drop-shadow(0 0 8px ${designSettings.accentColor}cc) drop-shadow(0 0 3px ${designSettings.textColor}aa)`,
              width: '100%', height: '100%',
            }">
              <path
                d="M12 1.5 L13.8 9.8 L22.5 12 L13.8 14.2 L12 22.5 L10.2 14.2 L1.5 12 L10.2 9.8 Z"
                :fill="i % 2 === 0 ? designSettings.accentColor : designSettings.textColor"
                opacity="0.92"
              />
            </svg>
          </div>
        </template>
      </template>

      <!-- Dotted Grid (Sharper + Crisp Larger Dots, Vignette so center is CLEAR) -->
      <template v-else-if="designSettings.bgAnimation === 'grid-dots'">
        <div
          :style="{
            position: 'absolute', inset: 0,
            backgroundImage: `radial-gradient(${designSettings.textColor} 1.8px, transparent 1.8px)`,
            backgroundSize: '28px 28px',
            opacity: 0.55,
            maskImage: 'radial-gradient(ellipse 130% 95% at center, black 55%, transparent 92%)',
            WebkitMaskImage: 'radial-gradient(ellipse 130% 95% at center, black 55%, transparent 92%)',
          }"
        ></div>
        <div
          :style="{
            position: 'absolute', inset: 0,
            backgroundImage: `radial-gradient(${designSettings.accentColor} 1.2px, transparent 1.2px)`,
            backgroundSize: '56px 56px',
            backgroundPosition: '14px 14px',
            opacity: 0.35,
            maskImage: 'radial-gradient(ellipse 130% 95% at center, black 50%, transparent 92%)',
            WebkitMaskImage: 'radial-gradient(ellipse 130% 95% at center, black 50%, transparent 92%)',
          }"
        ></div>
      </template>
      <!-- NEW 9: Fireflies Amber Glow -->
      <template v-else-if="designSettings.bgAnimation === 'fireflies'">
        <div v-for="(s,i) in 24" :key="'firefly'+i" class="pointer-events-none absolute rounded-full" :style="{
          width: (4 + (i%3)*3) + 'px',
          height: (4 + (i%3)*3) + 'px',
          left: ((i*37.2) % 100) + '%',
          top: ((i*53.7) % 100) + '%',
          background: `radial-gradient(circle, ${hexToRgba('#e8d9b8', 1)} 0%, ${hexToRgba('#c2a068', 0.95)} 50%, transparent 75%)`,
          boxShadow: `0 0 ${10 + (i%4)*5}px ${hexToRgba('#c2a068', 0.62)}`,
          opacity: (0.35 + ((i*7)%55)/100),
          animation: `fireflyFloat ${9 + (i%4)*2.4}s ease-in-out ${(i%8)*0.45}s infinite alternate, twinkle ${3 + (i%5)*0.5}s ease-in-out ${(i%6)*0.2}s infinite`,
        }"></div>
      </template>
      <!-- NEW 10: Bokeh Dreamy Circles (brass glow) -->
      <template v-else-if="designSettings.bgAnimation === 'bokeh'">
        <div class="pointer-events-none absolute rounded-full blur-[42px]" style="width:56%;height:56%;left:6%;top:12%;background:radial-gradient(circle,rgba(169, 133, 77,0.62) 0%,transparent 72%);"></div>
        <div class="pointer-events-none absolute rounded-full blur-[48px]" style="width:62%;height:62%;right:2%;top:22%;background:radial-gradient(circle,rgba(169, 133, 77,0.58) 0%,transparent 72%);"></div>
        <div class="pointer-events-none absolute rounded-full blur-[44px]" style="width:54%;height:54%;left:18%;bottom:4%;background:radial-gradient(circle,rgba(169, 133, 77,0.6) 0%,transparent 72%);"></div>
        <div class="pointer-events-none absolute rounded-full blur-[50px]" style="width:58%;height:58%;right:10%;bottom:8%;background:radial-gradient(circle,rgba(194, 160, 104,0.55) 0%,transparent 72%);"></div>
        <div class="pointer-events-none absolute rounded-full blur-[38px]" style="width:42%;height:42%;left:38%;top:36%;background:radial-gradient(circle,rgba(169, 133, 77,0.55) 0%,transparent 72%);"></div>
      </template>
      <!-- NEW 11: Liquid Metaball Blobs (4 moving gradient ellipses + 4 sizes + keyframes drift) -->
      <template v-else-if="designSettings.bgAnimation === 'liquid-blobs'">
        <div class="pointer-events-none absolute blur-[30px]" style="width:42%;height:34%;left:14%;top:14%;border-radius:56% 44% 60% 40%/52% 48% 52% 48%;background:radial-gradient(ellipse,rgba(169, 133, 77,0.72) 0%,transparent 70%);animation:liquidDriftA 22s ease-in-out infinite;"></div>
        <div class="pointer-events-none absolute blur-[34px]" style="width:46%;height:38%;right:8%;bottom:10%;border-radius:42% 58% 46% 54%/48% 52% 48% 52%;background:radial-gradient(ellipse,rgba(194, 160, 104,0.68) 0%,transparent 70%);animation:liquidDriftB 26s ease-in-out infinite;"></div>
        <div class="pointer-events-none absolute blur-[28px]" style="width:34%;height:28%;right:16%;top:10%;border-radius:50% 50% 42% 58%/56% 44% 56% 44%;background:radial-gradient(ellipse,rgba(169, 133, 77,0.65) 0%,transparent 70%);animation:liquidDriftA 19s ease-in-out -4s infinite reverse;"></div>
        <div class="pointer-events-none absolute blur-[32px]" style="width:40%;height:32%;left:10%;bottom:16%;border-radius:48% 52% 54% 46%/44% 56% 44% 56%;background:radial-gradient(ellipse,rgba(194, 160, 104,0.62) 0%,transparent 70%);animation:liquidDriftB 24s ease-in-out -3s infinite reverse;"></div>
      </template>
      <!-- NEW 12: Diagonal Brass Lines (criss-cross glow) -->
      <template v-else-if="designSettings.bgAnimation === 'neon-lines'">
        <div class="pointer-events-none absolute inset-0 opacity-75" style="background:repeating-linear-gradient(125deg,transparent 0 44px,linear-gradient(90deg,rgba(194, 160, 104,0.0),rgba(194, 160, 104,0.45) 50%,rgba(194, 160, 104,0.0)) 44px 48px),repeating-linear-gradient(55deg,transparent 0 60px,linear-gradient(90deg,rgba(169, 133, 77,0.0),rgba(169, 133, 77,0.38) 50%,rgba(169, 133, 77,0.0)) 60px 64px);"></div>
        <div class="pointer-events-none absolute inset-0" style="background:linear-gradient(180deg,transparent 0%,rgba(0,0,0,0.15) 45%,rgba(0,0,0,0.25) 100%);mask-image:radial-gradient(ellipse at center,black 35%,transparent 78%);-webkit-mask-image:radial-gradient(ellipse at center,black 35%,transparent 78%);"></div>
      </template>
      <!-- NEW 13: Snow Fall White (42 flakes 3 size tiers + fall keyframe) -->
      <template v-else-if="designSettings.bgAnimation === 'snow-fall'">
        <div v-for="(s,i) in 42" :key="'snow'+i" class="pointer-events-none absolute rounded-full bg-white" :style="{
          width: (2 + (i%3)*2) + 'px',
          height: (2 + (i%3)*2) + 'px',
          left: ((i*43.9) % 100) + '%',
          top: ((i*-13.1 + 500) % 110 - 10) + '%',
          opacity: (0.45 + ((i*11)%50)/100),
          boxShadow: ((i%3)===2)?'0 0 6px rgba(255,255,255,0.7)':'none',
          animation: `snowFall ${10 + (i%5)*3.2}s linear ${(i%9)*0.7}s infinite`,
        }"></div>
      </template>
      <!-- NEW 14: Cosmic Dust Purple + mini star sparkle -->
      <template v-else-if="designSettings.bgAnimation === 'cosmic-dust'">
        <div class="pointer-events-none absolute blur-[36px]" style="width:52%;height:52%;left:8%;top:10%;background:radial-gradient(circle,rgba(169, 133, 77,0.55) 0%,transparent 72%);animation:auroraShift 24s ease-in-out infinite alternate;"></div>
        <div class="pointer-events-none absolute blur-[40px]" style="width:56%;height:56%;right:0%;top:22%;background:radial-gradient(circle,rgba(138, 106, 51,0.48) 0%,transparent 72%);animation:auroraShift 28s ease-in-out -3s infinite alternate-reverse;"></div>
        <div class="pointer-events-none absolute blur-[34px]" style="width:48%;height:48%;left:22%;bottom:2%;background:radial-gradient(circle,rgba(194, 160, 104,0.55) 0%,transparent 72%);animation:auroraShift 22s ease-in-out -7s infinite alternate;"></div>
        <div class="pointer-events-none absolute blur-[38px]" style="width:50%;height:50%;right:14%;bottom:6%;background:radial-gradient(circle,rgba(169, 133, 77,0.5) 0%,transparent 72%);animation:auroraShift 30s ease-in-out -11s infinite alternate-reverse;"></div>
        <div v-for="(s,i) in 30" :key="'dusttwinkle'+i" class="pointer-events-none absolute rounded-full bg-white" :style="{
          width: (1.5 + (i%2)) + 'px', height: (1.5 + (i%2)) + 'px',
          left: ((i*61.3)%100)+'%', top: ((i*29.7)%100)+'%',
          opacity: (0.4 + ((i*13)%45)/100),
          animation: `twinkle ${2.6 + (i%4)*0.4}s ease-in-out ${(i%7)*0.25}s infinite`,
        }"></div>
      </template>
      <!-- NEW 15: Confetti Pop Color (rainbow 8 colors + 58 dots + bouncePulse keyframe mix) -->
      <template v-else-if="designSettings.bgAnimation === 'confetti-pop'">
        <div v-for="(c,i) in 58" :key="'confetti'+i" class="pointer-events-none absolute" :style="{
          width: (3 + (i%3)*2.2) + 'px', height: (6 + (i%4)*2) + 'px',
          left: ((i*31.7)%100) + '%',
          top: ((i*47.3)%100) + '%',
          background: ['#8a6a33','#c2a068','#a9854d','#c2a068','#a9854d','#8a6a33','#a9854d','#c2a068'][i%8],
          borderRadius: ((i%5)===0)?'50%':((i%3)===1)?'1px':'4px',
          transform: `rotate(${(i*19)%360}deg)`,
          opacity: (0.5 + ((i*17)%45)/100),
          animation: `bouncePulse ${3.6 + (i%6)*0.3}s ease-in-out ${(i%8)*0.2}s infinite`,
        }"></div>
      </template>
    </div>

    <!-- Back to Home Button -->
    <Transition name="fade-slide">
      <button
        v-if="showHomeButton"
        @click="goToHome"
        @mouseover="handleBackButtonHoverIn"
        @mouseout="handleBackButtonHoverOut"
        aria-label="Go back to home page"
        role="button"
        tabindex="0"
        :style="{
          position: 'fixed',
          top: `calc(${isPreviewMode ? '22px' : (isSmallMobile ? '10px' : isMobile ? '12px' : '18px')} + var(--sat, 0px))`,
          left: `calc(${isSmallMobile ? '10px' : isMobile ? '12px' : '20px'} + var(--sal, 0px))`,
          zIndex: 1000,
          display: 'flex',
          alignItems: 'center',
          gap: isSmallMobile ? '6px' : isMobile ? '8px' : '10px',
          padding: isSmallMobile ? '8px 12px' : isMobile ? '10px 14px' : '12px 20px',
          background: 'rgba(255, 255, 255, 0.1)',
          backdropFilter: 'blur(20px)',
          WebkitBackdropFilter: 'blur(20px)',
          border: '1px solid rgba(255, 255, 255, 0.2)',
          borderRadius: isMobile ? '12px' : '15px',
          color: responsive.textPrimary,
          fontSize: isSmallMobile ? '13px' : isMobile ? '14px' : '16px',
          fontWeight: 600,
          cursor: 'pointer',
          transition: 'all 0.3s',
          boxShadow: '0 4px 15px rgba(0, 0, 0, 0.2)',
          willChange: 'opacity, transform',
          maxWidth: `calc(50vw - ${isSmallMobile ? '16px' : isMobile ? '20px' : '40px'} - var(--sal, 0px) - var(--sar, 0px))`,
          overflow: 'hidden',
        }"
      >
        <Icon
          name="heroicons:arrow-left"
          :class="['flex-shrink-0', isSmallMobile ? 'w-4 h-4' : isMobile ? 'w-[18px] h-[18px]' : 'w-5 h-5']"
          aria-hidden="true"
        />
        <span
          :style="{
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap',
          }"
        >Home</span>
      </button>
    </Transition>

    <!-- Edit Profile Button (for authenticated card owners) -->
    <Transition name="fade-slide">
      <button
        v-if="showEditButton && !loading && !profileNotFound"
        @click="goToEditProfile"
        @mouseover="handleEditButtonHoverIn"
        @mouseout="handleEditButtonHoverOut"
        aria-label="Edit your profile in dashboard"
        role="button"
        tabindex="0"
        :style="{
          position: 'fixed',
          top: `calc(${isPreviewMode ? '22px' : (isSmallMobile ? '10px' : isMobile ? '12px' : '18px')} + var(--sat, 0px))`,
          right: `calc(${isSmallMobile ? '10px' : isMobile ? '12px' : '20px'} + var(--sar, 0px))`,
          zIndex: 1000,
          display: 'flex',
          alignItems: 'center',
          gap: isSmallMobile ? '6px' : isMobile ? '8px' : '10px',
          padding: isSmallMobile ? '8px 12px' : isMobile ? '10px 14px' : '12px 20px',
          background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
          backdropFilter: 'blur(20px)',
          WebkitBackdropFilter: 'blur(20px)',
          border: '1px solid rgba(255, 255, 255, 0.3)',
          borderRadius: isMobile ? '12px' : '15px',
          color: '#1b1407',
          fontSize: isSmallMobile ? '13px' : isMobile ? '14px' : '16px',
          fontWeight: 600,
          cursor: 'pointer',
          transition: 'all 0.3s',
          boxShadow: '0 4px 15px rgba(194, 160, 104, 0.4)',
          willChange: 'opacity, transform',
          maxWidth: `calc(50vw - ${isSmallMobile ? '16px' : isMobile ? '20px' : '40px'} - var(--sal, 0px) - var(--sar, 0px))`,
          overflow: 'hidden',
        }"
      >
        <Icon
          name="heroicons:pencil-square"
          :class="['flex-shrink-0', isSmallMobile ? 'w-4 h-4' : isMobile ? 'w-[18px] h-[18px]' : 'w-5 h-5']"
          aria-hidden="true"
        />
        <span
          :style="{
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap',
          }"
        >Edit Profile</span>
      </button>
    </Transition>

    <div
      :style="{
        position: 'relative',
        zIndex: 1,
        width: '100%',
        maxWidth: responsive.pageMaxWidth || '1200px',
        margin: '0 auto',
        padding: responsive.containerPadding,
        boxSizing: 'border-box',
      }"
    >
      <!-- Loading State (skeleton mirrors the profile structure) -->
      <div
        v-if="loading"
        :style="{ width: '100%', padding: '40px 20px' }"
        aria-busy="true"
        aria-live="polite"
      >
        <div
          class="animate-pulse"
          :style="{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            gap: '18px',
            maxWidth: '560px',
            margin: '0 auto',
          }"
        >
          <div
            :class="skeletonBarClass"
            :style="{
              width: isSmallMobile ? '96px' : isMobile ? '112px' : '128px',
              height: isSmallMobile ? '96px' : isMobile ? '112px' : '128px',
              borderRadius: '9999px',
            }"
          />
          <div :class="skeletonBarClass" :style="{ width: '60%', height: '26px', borderRadius: '9999px' }" />
          <div :class="skeletonBarClass" :style="{ width: '40%', height: '16px', borderRadius: '9999px' }" />
          <div :class="skeletonBarClass" :style="{ width: '100%', height: '112px', borderRadius: '20px', marginTop: '12px' }" />
          <div :class="skeletonBarClass" :style="{ width: '100%', height: '112px', borderRadius: '20px' }" />
          <div :class="skeletonBarClass" :style="{ width: '100%', height: '72px', borderRadius: '20px' }" />
        </div>
      </div>

      <!-- No Profile Data Yet -->
      <div
        v-else-if="noProfileData"
        :style="{
          textAlign: 'center',
          padding: '80px 20px',
          maxWidth: '600px',
          margin: '0 auto',
        }"
      >
        <div :style="{ marginBottom: '20px' }">
          <Icon name="heroicons:document-text" class="w-20 h-20 text-white/60 mx-auto" aria-hidden="true" />
        </div>
        <h2 :style="{ fontSize: responsive.h2Size, marginBottom: '15px', color: responsive.textPrimary }">
          No Profile Data Yet
        </h2>
        <p
          :style="{
            fontSize: responsive.bodySize,
            color: responsive.textMuted,
            marginBottom: '30px',
            lineHeight: '1.6',
          }"
        >
          This NFC card hasn't been set up with profile information yet.
          <br />
          Please use the <strong>Profile Builder</strong> to create your landing page.
        </p>
        <button
          v-if="isCardOwner"
          @click="goToEditProfile"
          :style="{
            padding: '15px 30px',
            background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
            color: '#1b1407',
            border: 'none',
            borderRadius: '50px',
            fontSize: responsive.bodySize,
            fontWeight: 700,
            cursor: 'pointer',
            boxShadow: '0 10px 30px -14px rgba(194, 160, 104, 0.7)',
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => { e.target.style.transform = 'translateY(-2px)'; e.target.style.boxShadow = '0 15px 40px rgba(194, 160, 104, 0.4)'; }"
          @mouseout="(e) => { e.target.style.transform = 'translateY(0)'; e.target.style.boxShadow = '0 10px 30px rgba(194, 160, 104, 0.3)'; }"
        >
          Set Up Profile Now
          <Icon name="heroicons:arrow-right" class="w-4 h-4 inline-block align-[-0.2em]" aria-hidden="true" />
        </button>
      </div>

      <!-- Profile Not Found -->
      <div
        v-else-if="profileNotFound"
        :style="{ textAlign: 'center', padding: '60px 20px', color: responsive.textPrimary }"
      >
        <div :style="{ marginBottom: '20px' }">
          <Icon name="heroicons:exclamation-circle" class="w-[72px] h-[72px] text-white/60 mx-auto" aria-hidden="true" />
        </div>
        <h2 :style="{ fontSize: responsive.h2Size, marginBottom: '15px' }">
          Profile Not Found
        </h2>
        <p
          :style="{
            fontSize: responsive.bodySize,
            color: responsive.textMuted,
            marginBottom: '30px',
          }"
        >
          This profile doesn't exist or has been removed.
        </p>
        <NuxtLink
          to="/Homepage"
          :style="{
            display: 'inline-flex',
            alignItems: 'center',
            gap: '8px',
            padding: '14px 28px',
            background: 'rgba(255, 255, 255, 0.1)',
            border: '1px solid rgba(255, 255, 255, 0.2)',
            borderRadius: '50px',
            color: responsive.textPrimary,
            fontSize: responsive.bodySize,
            fontWeight: 600,
            textDecoration: 'none',
          }"
        >
          <Icon name="heroicons:home" class="w-5 h-5 flex-shrink-0" aria-hidden="true" />
          Back to Home
        </NuxtLink>
      </div>

      <!-- Profile Content -->
      <template v-else>
        <!-- Hero Section - Enhanced -->
        <div
          class="profile-glow"
          :style="{
            position: 'relative',
            background: responsive.cardBackground,
            backdropFilter: `blur(${responsive.cardBackdropBlur})`,
            WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
            border: responsive.cardBorder,
            borderRadius: responsive.borderRadius,
            padding: '0',
            marginBottom: responsive.sectionGap,
            overflow: 'hidden',
            boxShadow: responsive.cardShadow,
          }"
        >
          <!-- Cover Banner (if available) -->
          <div
            v-if="coverBanner"
            :style="{
              position: 'absolute',
              inset: 0,
              backgroundImage: `url(${coverBanner})`,
              backgroundSize: 'cover',
              backgroundPosition: 'center',
              transform: 'scale(1.02)',
              filter: 'brightness(0.9)',
            }"
          >
            <div
              :style="{
                position: 'absolute',
                inset: 0,
                background: 'linear-gradient(to bottom, rgba(0,0,0,0.25) 0%, rgba(3,7,18,0.9) 100%)',
              }"
            />
          </div>
          <!-- Default hero backdrop — keeps the first screen art-directed when no cover image is uploaded -->
          <div
            v-else
            aria-hidden="true"
            :style="{
              position: 'absolute',
              inset: 0,
              background: `radial-gradient(120% 90% at 50% -20%, ${hexToRgba(designSettings.accentColor, 0.26)} 0%, transparent 58%), radial-gradient(70% 60% at 86% 0%, ${hexToRgba(designSettings.accentColor, 0.14)} 0%, transparent 62%), linear-gradient(180deg, rgba(6, 9, 13, 0) 44%, rgba(6, 9, 13, 0.55) 100%)`,
              pointerEvents: 'none',
            }"
          />

          <!-- NFCGo Watermark (shown when the plan/admin policy includes branding) -->
          <div
            v-if="showNfcgoBranding"
            :style="{
              position: 'absolute',
              top: isSmallMobile ? '10px' : isMobile ? '12px' : '16px',
              left: isSmallMobile ? '10px' : isMobile ? '12px' : '16px',
              zIndex: 10,
              display: 'flex',
              alignItems: 'center',
              gap: isSmallMobile ? '6px' : '8px',
              padding: isSmallMobile ? '5px 10px' : isMobile ? '6px 12px' : '8px 14px',
              background: 'rgba(255, 255, 255, 0.95)',
              borderRadius: isSmallMobile ? '6px' : '8px',
              boxShadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
            }"
          >
            <NFCGoLogo variant="light" :size="isSmallMobile ? 'xs' : 'sm'" />
          </div>

          <!-- Hero content — asymmetric editorial masthead -->
          <div
            :style="{
              padding:
                isSmallMobile ? '64px 16px 34px'
                : isPhablet ? '68px 20px 38px'
                : isMobile ? '74px 26px 42px'
                : isTablet ? '86px 44px 54px'
                : isSmallLaptop ? '94px 52px 62px'
                : '106px 64px 70px',
              position: 'relative',
              zIndex: 1,
              paddingTop: !showNfcgoBranding
                ? (isSmallMobile ? '32px' : isPhablet ? '36px' : isMobile ? '42px' : isTablet ? '54px' : '62px')
                : undefined,
            }"
          >
          <div
            :style="{
              position: 'relative',
              zIndex: 2,
              display: 'grid',
              gridTemplateColumns: responsive.heroTwoCol ? 'minmax(0, 1.04fr) minmax(0, 0.96fr)' : 'minmax(0, 1fr)',
              alignItems: 'center',
              columnGap: isLargeDesktop ? '76px' : '52px',
              rowGap: isSmallMobile ? '26px' : '34px',
            }"
          >
            <!-- Portrait plate — a first-class art-directed element, never a small avatar -->
            <div
              :style="{
                order: responsive.heroTwoCol ? 2 : 1,
                gridColumn: responsive.heroTwoCol ? '2' : '1',
                gridRow: 1,
                position: 'relative',
                width: responsive.portraitSize,
                maxWidth: '82%',
                aspectRatio: '1 / 1',
                justifySelf: 'center',
                cursor: 'pointer',
              }"
              @mouseenter="imageHover = true"
              @mouseleave="imageHover = false"
            >
              <!-- soft brass halo behind the plate -->
              <div
                aria-hidden="true"
                :style="{
                  position: 'absolute',
                  inset: '-14%',
                  borderRadius: '50%',
                  background: `radial-gradient(circle at 50% 42%, ${hexToRgba(designSettings.accentColor, 0.28)} 0%, transparent 66%)`,
                  filter: 'blur(26px)',
                  opacity: imageHover ? 1 : 0.82,
                  transition: 'opacity 0.4s ease',
                  pointerEvents: 'none',
                }"
              />
              <!-- brass gradient ring -->
              <div
                aria-hidden="true"
                :style="{
                  position: 'absolute',
                  inset: 0,
                  borderRadius: '50%',
                  background: 'conic-gradient(from 205deg, #f7e2a1 0%, #c2a068 26%, #8a6a33 50%, #c2a068 74%, #f7e2a1 100%)',
                  transform: imageHover ? 'rotate(12deg)' : 'rotate(0deg)',
                  transition: 'transform 0.7s cubic-bezier(0.4, 0, 0.2, 1)',
                }"
              />
              <!-- inner ink disc that insets the portrait inside the ring -->
              <div
                aria-hidden="true"
                :style="{ position: 'absolute', inset: '3px', borderRadius: '50%', background: '#0a0e14' }"
              />
              <!-- uploaded photo, or a designed monogram plate when no image exists -->
              <img
                v-if="heroPortrait"
                :src="heroPortrait"
                alt="Profile photo"
                :style="{
                  position: 'absolute',
                  inset: '11px',
                  width: 'calc(100% - 22px)',
                  height: 'calc(100% - 22px)',
                  borderRadius: '50%',
                  objectFit: 'cover',
                  transform: imageHover ? 'scale(1.04)' : 'scale(1)',
                  transition: 'transform 0.55s cubic-bezier(0.4, 0, 0.2, 1)',
                }"
              />
              <div
                v-else
                aria-hidden="true"
                :style="{
                  position: 'absolute',
                  inset: '11px',
                  borderRadius: '50%',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  background: `radial-gradient(circle at 50% 28%, ${hexToRgba(designSettings.accentColor, 0.22)} 0%, rgba(10, 14, 20, 0.98) 64%)`,
                }"
              >
                <span
                  :style="{
                    fontFamily: responsive.displayFont,
                    fontStyle: 'italic',
                    fontWeight: 500,
                    lineHeight: 1,
                    letterSpacing: '0.02em',
                    color: '#e8d9b8',
                    fontSize: isSmallMobile ? '52px' : isPhablet ? '60px' : isMobile ? '70px' : isTablet ? '82px' : isLargeDesktop ? '112px' : '98px',
                  }"
                >{{ heroInitials }}</span>
              </div>
            </div>

            <!-- Text column -->
            <div
              :style="{
                order: responsive.heroTwoCol ? 1 : 2,
                gridColumn: 1,
                gridRow: responsive.heroTwoCol ? 1 : 2,
                display: 'flex',
                flexDirection: 'column',
                alignItems: responsive.heroTwoCol ? 'flex-start' : 'center',
                textAlign: responsive.heroTwoCol ? 'left' : 'center',
                minWidth: 0,
              }"
            >
              <h1
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.nameSize,
                  fontWeight: 600,
                  color: responsive.textPrimary,
                  marginBottom: isSmallMobile ? '14px' : isMobile ? '18px' : '22px',
                  letterSpacing: '-0.022em',
                  lineHeight: 0.98,
                  overflowWrap: 'break-word',
                  wordBreak: 'break-word',
                  hyphens: 'auto',
                  order: 1,
                  maxWidth: '100%',
                }"
              >
                <span
                  v-if="heroNameLines.leading"
                  :style="{
                    display: 'block',
                    fontStyle: 'italic',
                    fontWeight: 300,
                    color: hexToRgba(designSettings.accentColor, 0.95),
                    letterSpacing: '-0.01em',
                  }"
                >{{ heroNameLines.leading }}</span>
                <!-- Cream metallic text on dark themes; on light themes it
                     would vanish, so it uses the theme's text colour. -->
                <span
                  :style="isLightTheme
                    ? { display: 'block', fontWeight: 600, color: responsive.textPrimary }
                    : {
                        display: 'block',
                        fontWeight: 600,
                        backgroundImage: 'linear-gradient(180deg, #ffffff 0%, #f7f5f0 46%, #e8d9b8 100%)',
                        WebkitBackgroundClip: 'text',
                        backgroundClip: 'text',
                        WebkitTextFillColor: 'transparent',
                      }"
                >{{ heroNameLines.trailing || heroNameLines.leading }}</span>
              </h1>

            <div
              :style="{
                display: 'flex',
                gap: isSmallMobile ? '8px' : '10px',
                justifyContent: responsive.heroTwoCol ? 'flex-start' : 'center',
                flexWrap: 'wrap',
                rowGap: isSmallMobile ? '8px' : '10px',
                order: 2,
                maxWidth: '100%',
              }"
            >
              <div
                v-if="profile.qualification"
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: isSmallMobile ? '6px' : isMobile ? '8px' : '10px',
                  padding: responsive.badgePadding,
                  borderRadius: '50px',
                  fontSize: responsive.smallSize,
                  fontWeight: 600,
                  backdropFilter: 'blur(10px)',
                  WebkitBackdropFilter: 'blur(10px)',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.38)}`,
                  background: hexToRgba(designSettings.accentColor, 0.08),
                  color: responsive.textPrimary,
                  animation: 'fadeInUp 0.6s ease-out forwards',
                  opacity: 0,
                  animationDelay: '0.3s',
                  maxWidth: '100%',
                  overflowWrap: 'break-word',
                }"
              >
                <Icon name="heroicons:academic-cap" class="flex-shrink-0" :style="{ width: isSmallMobile ? '14px' : isMobile ? '16px' : '18px', height: isSmallMobile ? '14px' : isMobile ? '16px' : '18px', color: designSettings.accentColor }" aria-hidden="true" />
                <span :style="{ overflow: 'hidden', textOverflow: 'ellipsis', minWidth: 0 }">{{ profile.qualification }}</span>
              </div>
              <div
                v-if="profile.position"
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: isSmallMobile ? '6px' : isMobile ? '8px' : '10px',
                  padding: responsive.badgePadding,
                  borderRadius: '50px',
                  fontSize: responsive.smallSize,
                  fontWeight: 600,
                  backdropFilter: 'blur(10px)',
                  WebkitBackdropFilter: 'blur(10px)',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.38)}`,
                  background: hexToRgba(designSettings.accentColor, 0.08),
                  color: responsive.textPrimary,
                  animation: 'fadeInUp 0.6s ease-out forwards',
                  opacity: 0,
                  animationDelay: '0.4s',
                  maxWidth: '100%',
                  overflowWrap: 'break-word',
                }"
              >
                <Icon name="heroicons:briefcase" class="flex-shrink-0" :style="{ width: isSmallMobile ? '14px' : isMobile ? '16px' : '18px', height: isSmallMobile ? '14px' : isMobile ? '16px' : '18px', color: designSettings.accentColor }" aria-hidden="true" />
                <span :style="{ overflow: 'hidden', textOverflow: 'ellipsis', minWidth: 0 }">{{ profile.position }}</span>
              </div>
              <!-- Pronouns Badge -->
              <div
                v-if="profile.pronouns"
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: isSmallMobile ? '6px' : isMobile ? '8px' : '10px',
                  padding: responsive.badgePadding,
                  borderRadius: '50px',
                  fontSize: responsive.smallSize,
                  fontWeight: 600,
                  backdropFilter: 'blur(10px)',
                  WebkitBackdropFilter: 'blur(10px)',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.38)}`,
                  background: hexToRgba(designSettings.accentColor, 0.08),
                  color: responsive.textPrimary,
                  animation: 'fadeInUp 0.6s ease-out forwards',
                  opacity: 0,
                  animationDelay: '0.5s',
                  maxWidth: '100%',
                  overflowWrap: 'break-word',
                }"
              >
                <Icon name="heroicons:user" class="flex-shrink-0" :style="{ width: isSmallMobile ? '14px' : isMobile ? '16px' : '18px', height: isSmallMobile ? '14px' : isMobile ? '16px' : '18px', color: designSettings.accentColor }" aria-hidden="true" />
                <span :style="{ overflow: 'hidden', textOverflow: 'ellipsis', minWidth: 0 }">{{ profile.pronouns }}</span>
              </div>
            </div>

            <!-- Hero identity meta — company · location -->
            <div
              v-if="heroMeta.length"
              :style="{
                display: 'flex',
                alignItems: 'center',
                justifyContent: responsive.heroTwoCol ? 'flex-start' : 'center',
                flexWrap: 'wrap',
                gap: isSmallMobile ? '10px' : isPhablet ? '12px' : '14px',
                marginTop: isSmallMobile ? '18px' : isPhablet ? '20px' : '24px',
                marginBottom: isSmallMobile ? '6px' : isPhablet ? '8px' : '10px',
                animation: 'fadeInUp 0.6s ease-out forwards',
                opacity: 0,
                animationDelay: '0.52s',
                order: 3,
              }"
            >
              <template v-for="(item, i) in heroMeta" :key="item.label">
                <span
                  v-if="i > 0"
                  aria-hidden="true"
                  :style="{ width: '4px', height: '4px', borderRadius: '50%', background: responsive.textMuted, opacity: 0.55 }"
                />
                <span
                  :style="{
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '6px',
                    color: responsive.textMuted,
                    fontSize: responsive.smallSize,
                    fontWeight: 500,
                    letterSpacing: '0.2px',
                    maxWidth: '100%',
                    overflowWrap: 'break-word',
                  }"
                >
                  <Icon :name="item.icon" :style="{ width: '14px', height: '14px', color: designSettings.accentColor }" aria-hidden="true" />
                  {{ item.label }}
                </span>
              </template>
            </div>

            <!-- Tagline -->
            <p
              v-if="profile.tagline"
              :style="{
                color: responsive.textMuted,
                fontSize: responsive.bodySize,
                fontStyle: 'italic',
                marginTop: isSmallMobile ? '14px' : isPhablet ? '16px' : '20px',
                textAlign: responsive.heroTwoCol ? 'left' : 'center',
                animation: 'fadeInUp 0.6s ease-out forwards',
                opacity: 0,
                animationDelay: '0.6s',
                lineHeight: 1.6,
                overflowWrap: 'break-word',
                wordBreak: 'break-word',
                padding: responsive.heroTwoCol ? 0 : '0 6px',
                maxWidth: responsive.heroTwoCol ? '46ch' : '100%',
                order: 4,
              }"
            >
              "{{ profile.tagline }}"
            </p>

            <!-- Hero primary action — the single most important contact gesture -->
            <div
              v-if="isFeatureEnabled('vcard_download') || isFeatureEnabled('social_sharing')"
              :style="{
                display: 'flex',
                flexWrap: 'wrap',
                gap: isSmallMobile ? '10px' : '12px',
                marginTop: isSmallMobile ? '22px' : '28px',
                justifyContent: responsive.heroTwoCol ? 'flex-start' : 'center',
                width: '100%',
                animation: 'fadeInUp 0.6s ease-out forwards',
                opacity: 0,
                animationDelay: '0.68s',
                order: 5,
              }"
            >
              <button
                v-if="isFeatureEnabled('vcard_download')"
                @click="saveContact"
                @mouseover="(e) => handleHoverTransform(e, 'translateY(-2px)')"
                @mouseout="(e) => handleHoverTransform(e, 'translateY(0)')"
                :style="{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '10px',
                  padding: isMobile ? '14px 24px' : '15px 30px',
                  borderRadius: '999px',
                  border: 'none',
                  background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
                  color: '#1b1407',
                  fontSize: isMobile ? '14px' : '15px',
                  fontWeight: 700,
                  letterSpacing: '0.01em',
                  cursor: 'pointer',
                  boxShadow: '0 16px 38px -12px rgba(194, 160, 104, 0.75)',
                  transition: 'transform 0.25s ease, box-shadow 0.25s ease',
                }"
              >
                <Icon name="heroicons:user-plus" :style="{ width: '18px', height: '18px' }" aria-hidden="true" />
                Add to Contacts
              </button>
              <button
                v-if="isFeatureEnabled('social_sharing')"
                @click="showSocialShare = true"
                @mouseover="(e) => handleHoverTransform(e, 'translateY(-2px)')"
                @mouseout="(e) => handleHoverTransform(e, 'translateY(0)')"
                :style="{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '10px',
                  padding: isMobile ? '14px 22px' : '15px 26px',
                  borderRadius: '999px',
                  background: 'rgba(255, 255, 255, 0.04)',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`,
                  color: responsive.textPrimary,
                  fontSize: isMobile ? '14px' : '15px',
                  fontWeight: 600,
                  cursor: 'pointer',
                  transition: 'transform 0.25s ease, background 0.25s ease',
                }"
              >
                <Icon name="heroicons:share" :style="{ width: '17px', height: '17px', color: designSettings.accentColor }" aria-hidden="true" />
                Share Profile
              </button>
            </div>
          </div>

          </div>
        </div>
        </div>

        <!-- Quick Actions -->
        <div
          v-if="quickActions.length > 0"
          class="quick-actions"
          :style="{
            display: 'flex',
            justifyContent: 'center',
            gap: responsive.actionGap,
            marginBottom: responsive.sectionGap,
            flexWrap: 'wrap',
            rowGap: isSmallMobile ? '8px' : '10px',
          }"
        >
          <button
            v-for="(action, idx) in quickActions"
            :key="idx"
            @click="handleAction(action.action)"
            @mouseover="(e) => handleHoverTransform(e, 'translateY(-5px)')"
            @mouseout="(e) => handleHoverTransform(e, 'translateY(0)')"
            :style="{
              position: 'relative',
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: isSmallMobile ? '6px' : '8px',
              padding: responsive.actionPadding,
              minWidth: isSmallMobile ? '62px' : isMobile ? '70px' : '80px',
              flex: isSmallMobile ? '1 1 calc(50% - 8px)' : undefined,
              maxWidth: isSmallMobile ? 'calc(50% - 8px)' : undefined,
              background: `linear-gradient(180deg, ${hexToRgba(designSettings.accentColor, 0.12)} 0%, ${hexToRgba(designSettings.accentColor, 0.04)} 100%)`,
              backdropFilter: 'blur(10px)',
              WebkitBackdropFilter: 'blur(10px)',
              border: `1px solid ${hexToRgba(designSettings.accentColor, 0.28)}`,
              borderRadius: isMobile ? '15px' : '20px',
              color: responsive.textPrimary,
              fontWeight: 600,
              cursor: 'pointer',
              overflow: 'hidden',
              animation: 'slideInUp 0.6s ease-out backwards',
              animationDelay: `${idx * 0.1}s`,
              transition: 'all 0.3s',
            }"
          >
            <Icon
              :name="action.icon"
              :style="{
                width: responsive.actionIconSize,
                height: responsive.actionIconSize,
                lineHeight: 1,
                flexShrink: 0,
                color: designSettings.accentColor,
              }"
              aria-hidden="true"
            />
            <span
              :style="{
                fontSize: responsive.smallSize,
                textAlign: 'center',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                lineHeight: 1.2,
                maxWidth: '100%',
              }"
            >{{ action.label }}</span>
          </button>
        </div>

        <!-- Main Content Grid -->
        <div class="section-grid" :style="{ display: 'grid', gap: responsive.sectionGap }">
          <!-- Company Card (Combined: Company Info + Team) -->
          <div
            v-if="showCompanySection || showTeamSection"
            ref="companyCardRef"
            @mousemove="!isMobile && handleCardTilt"
            @mouseleave="!isMobile && resetCardTilt"
            :style="{
              position: 'relative',
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
              overflow: 'hidden',
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:building-office" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Company
              </h2>
            </div>
            
            <!-- Company Info Sub-section -->
            <div
              v-if="hasCompanyData"
              :style="{
                marginBottom: showTeamSection ? responsive.subSectionGap : '0',
              }"
            >
              <div
                :style="{
                  display: 'flex',
                  alignItems: 'flex-start',
                  gap: isMobile ? '15px' : '30px',
                  flexWrap: 'wrap',
                }"
              >
                <!-- Company Logo -->
                <div
                  v-if="isFieldVisible('companyTeam', 'companyLogo')"
                  :style="{
                    position: 'relative',
                    width: isMobile ? '80px' : '100px',
                    height: isMobile ? '80px' : '100px',
                    flexShrink: 0,
                  }"
                >
                  <!-- If company has logo image -->
                  <img
                    v-if="company.logo"
                    :src="company.logo"
                    alt="Company Logo"
                    :style="{
                      width: '100%',
                      height: '100%',
                      objectFit: 'cover',
                      borderRadius: isMobile ? '15px' : '18px',
                      border: `1px solid ${hexToRgba(designSettings.accentColor, 0.35)}`,
                    }"
                  />
                  <!-- Fallback to logo text -->
                  <template v-else>
                    <div
                      :style="{
                        position: 'absolute',
                        inset: 0,
                        background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.18)} 0%, rgba(10, 14, 20, 0.6) 100%)`,
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`,
                        borderRadius: isMobile ? '15px' : '18px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: isMobile ? '22px' : '28px',
                        fontFamily: responsive.displayFont,
                        fontWeight: 600,
                        letterSpacing: '-0.01em',
                        color: chipTextColor,
                        zIndex: 1,
                        textAlign: 'center',
                        padding: '6px',
                        lineHeight: 1.1,
                        overflowWrap: 'break-word',
                      }"
                    >
                      {{ company.logoText }}
                    </div>
                  </template>
                </div>
                
                <!-- Company Info -->
                <div :style="{ flex: 1, minWidth: isMobile ? '100%' : 'auto' }">
                  <h3
                    v-if="company.name && isFieldVisible('companyTeam', 'companyName')"
                    :style="{
                      fontSize: responsive.h3Size,
                      fontWeight: 600,
                      letterSpacing: '-0.01em',
                      color: responsive.textPrimary,
                      marginBottom: '10px',
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                    }"
                  >
                    {{ company.name }}
                  </h3>
                  
                  <!-- Registration No -->
                  <p
                    v-if="company.registrationNo && isFieldVisible('companyTeam', 'companyRegNo')"
                    :style="{
                      color: responsive.textMuted,
                      fontSize: responsive.smallSize,
                      marginBottom: '8px',
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                    }"
                  >
                    <Icon name="heroicons:document-text" class="inline-block w-[1em] h-[1em] align-[-0.15em] mr-1" aria-hidden="true" />{{ company.registrationNo }}
                  </p>
                  
                  <!-- Industry & Year & Employee Count -->
                  <div
                    :style="{
                      display: 'flex',
                      gap: '12px',
                      flexWrap: 'wrap',
                      marginBottom: '12px',
                    }"
                  >
                    <span
                      v-if="company.industry && isFieldVisible('companyTeam', 'industry')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '5px',
                        padding: '4px 12px',
                        background: 'rgba(194, 160, 104, 0.2)',
                        borderRadius: '12px',
                        fontSize: responsive.smallSize,
                        color: responsive.textMuted,
                      }"
                    >
                      <Icon name="heroicons:building-storefront" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />{{ company.industry }}
                    </span>
                    <span
                      v-if="company.establishedYear && isFieldVisible('companyTeam', 'establishedYear')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '5px',
                        padding: '4px 12px',
                        background: 'rgba(232, 217, 184, 0.2)',
                        borderRadius: '12px',
                        fontSize: responsive.smallSize,
                        color: responsive.textMuted,
                      }"
                    >
                      <Icon name="heroicons:calendar" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />Est. {{ company.establishedYear }}
                    </span>
                    <span
                      v-if="company.employeeCount && isFieldVisible('companyTeam', 'employeeCount')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '5px',
                        padding: '4px 12px',
                        background: 'rgba(169, 133, 77, 0.2)',
                        borderRadius: '12px',
                        fontSize: responsive.smallSize,
                        color: responsive.textMuted,
                      }"
                    >
                      <Icon name="heroicons:users" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />{{ company.employeeCount }} employees
                    </span>
                  </div>
                  
                  <!-- Company Description -->
                  <p
                    v-if="company.description && isFieldVisible('companyTeam', 'companyDescription')"
                    :style="{
                      color: responsive.textMuted,
                      fontSize: responsive.bodySize,
                      lineHeight: '1.6',
                      marginBottom: '12px',
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                    }"
                    v-html="company.description"
                  />
                  <!-- Company Video (Business plan; YouTube/Vimeo) -->
                  <ProfileVideo
                    v-if="companyVideoEmbed && isFieldVisible('companyTeam', 'companyVideo')"
                    :src="companyVideoEmbed"
                    :title="`${company.name || 'Company'} video`"
                    :border-color="hexToRgba(designSettings.accentColor, 0.35)"
                    :style="{ margin: '6px 0 16px' }"
                  />
                  <div
                    v-if="company.department"
                    :style="{
                      display: 'inline-block',
                      padding: isMobile ? '8px 16px' : '10px 20px',
                      background: 'rgba(194, 160, 104, 0.2)',
                      border: '1px solid rgba(194, 160, 104, 0.4)',
                      borderRadius: '50px',
                      color: chipTextColor,
                      fontSize: responsive.smallSize,
                      fontWeight: 600,
                      maxWidth: '100%',
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                    }"
                  >
                    {{ company.department }}
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Team Sub-section -->
            <div
              v-if="showTeamSection"
              :style="{
                paddingTop: hasCompanyData ? responsive.subSectionGap : '0',
                borderTop: hasCompanyData ? `1px solid ${hexToRgba(designSettings.accentColor, 0.14)}` : 'none',
              }"
            >
              <div
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '12px',
                  marginBottom: '20px',
                }"
              >
                <div
                  :style="{
                    width: isMobile ? '32px' : '40px',
                    height: isMobile ? '32px' : '40px',
                    background: 'transparent',
                    border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                    borderRadius: '11px',
                    color: designSettings.accentColor,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '16px' : '20px',
                  }"
                >
                  <Icon name="heroicons:users" :style="{ width: isMobile ? '16px' : '20px', height: isMobile ? '16px' : '20px' }" aria-hidden="true" />
                </div>
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 600,
                    letterSpacing: '-0.01em',
                    color: responsive.textPrimary,
                    margin: 0,
                  }"
                >
                  Meet The Team
                </h3>
              </div>
              <div
                :style="{
                  display: 'grid',
                  gridTemplateColumns: responsive.servicesGrid,
                  gap: responsive.gridGap,
                }"
              >
                <component
                  :is="member.landing_page_url ? 'a' : 'div'"
                  v-for="(member, idx) in teamMembers"
                  :key="idx"
                  :href="member.landing_page_url || undefined"
                  :target="member.landing_page_url ? '_blank' : undefined"
                  @mouseenter="!isMobile && (activeTeam = idx)"
                  @mouseleave="!isMobile && (activeTeam = null)"
                  @touchstart="activeTeam = idx"
                  @touchend="setTimeout(() => (activeTeam = null), 2000)"
                  :style="{
                    textAlign: 'center',
                    padding: isMobile ? '20px 12px' : '25px 15px',
                    background: 'rgba(255, 255, 255, 0.05)',
                    border: '1px solid rgba(255, 255, 255, 0.1)',
                    borderRadius: isMobile ? '12px' : '15px',
                    transition: 'all 0.3s',
                    transform:
                      activeTeam === idx ? 'translateY(-10px)' : 'translateY(0)',
                    textDecoration: 'none',
                    display: 'block',
                    cursor: member.landing_page_url ? 'pointer' : 'default',
                  }"
                >
                  <div
                    :style="{
                      position: 'relative',
                      width: isMobile ? '60px' : '80px',
                      height: isMobile ? '60px' : '80px',
                      margin: '0 auto 15px',
                      transition: 'all 0.3s',
                    }"
                  >
                    <div
                      :style="{
                        position: 'absolute',
                        inset: '-4px',
                        borderRadius: '50%',
                        background: 'linear-gradient(135deg, #c2a068, #a9854d)',
                        opacity: activeTeam === idx ? 1 : 0,
                        transition: 'opacity 0.3s',
                        animation:
                          activeTeam === idx
                            ? 'rotate 2s linear infinite'
                            : 'none',
                      }"
                    />
                    <!-- Profile Image or Initials -->
                    <img
                      v-if="member.profile_image"
                      :src="member.profile_image"
                      :alt="member.name"
                      :style="{
                        position: 'absolute',
                        inset: 0,
                        width: '100%',
                        height: '100%',
                        borderRadius: '50%',
                        objectFit: 'cover',
                        border: '2px solid rgba(6, 9, 13, 0.5)',
                      }"
                    />
                    <div
                      v-else
                      :style="{
                        position: 'absolute',
                        inset: 0,
                        background:
                          'linear-gradient(135deg, rgba(194, 160, 104, 0.8), rgba(169, 133, 77, 0.8))',
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: isMobile ? '20px' : '28px',
                        fontWeight: 800,
                        color: responsive.textPrimary,
                        border: '2px solid rgba(6, 9, 13, 0.5)',
                      }"
                    >
                      {{ member.initials }}
                    </div>
                  </div>
                  <h4
                    :style="{
                      color: responsive.textPrimary,
                      fontSize: responsive.h4Size,
                      fontWeight: 700,
                      margin: '0 0 6px 0',
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                    }"
                  >
                    {{ member.name }}
                  </h4>
                  <p
                    :style="{
                      color: responsive.textMuted,
                      fontSize: responsive.smallSize,
                      margin: 0,
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                    }"
                  >
                    {{ member.role }}
                  </p>
                  <!-- View Profile Link Indicator -->
                  <p
                    v-if="member.landing_page_url"
                    :style="{
                      color: 'rgba(194, 160, 104, 0.8)',
                      fontSize: responsive.smallSize,
                      marginTop: '6px',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      gap: '4px',
                    }"
                  >
                    View Profile <Icon name="heroicons:arrow-right" class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" />
                  </p>
                </component>
              </div>
            </div>
          </div>

          <!-- About Card (Combined: About Me + Education + Awards) -->
          <div
            v-if="showAboutSection || showEducationSection || showAwardsSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
              transition: 'box-shadow 0.3s ease, border-color 0.3s ease',
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:sparkles" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                About Me
              </h2>
            </div>
            
            <!-- About Me Sub-section -->
            <div v-if="hasAboutData" :style="{ marginBottom: (showEducationSection || showAwardsSection) ? responsive.subSectionGap : '0' }">
              <div
                v-if="profile.bio && isFieldVisible('profileAchievements', 'bio')"
                :style="{ marginBottom: stats.length > 0 ? (isMobile ? '24px' : '34px') : '0' }"
              >
                <span
                  aria-hidden="true"
                  :style="{
                    display: 'block',
                    width: '52px',
                    height: '2px',
                    background: `linear-gradient(90deg, ${designSettings.accentColor} 0%, transparent 100%)`,
                    marginBottom: isMobile ? '14px' : '18px',
                  }"
                />
                <p
                  :style="{
                    color: responsive.textPrimary,
                    lineHeight: 1.72,
                    whiteSpace: 'normal',
                    overflowWrap: 'break-word',
                    fontSize: isMobile ? '17px' : '20px',
                    fontWeight: 400,
                    letterSpacing: '-0.005em',
                    maxWidth: '66ch',
                  }"
                >
                  {{ profile.bio }}
                </p>
              </div>
              <!-- Introduction Video (Premium/Business; YouTube/Vimeo) -->
              <ProfileVideo
                v-if="introVideoEmbed"
                :src="introVideoEmbed"
                :title="`${profile.name || 'Profile'} introduction video`"
                :border-color="hexToRgba(designSettings.accentColor, 0.35)"
                :style="{ marginBottom: stats.length > 0 ? (isMobile ? '24px' : '34px') : '0', maxWidth: '720px' }"
              />
              <div
                v-if="stats.length > 0 && isFieldVisible('profileAchievements', 'profileStats')"
                :style="{
                  display: 'grid',
                  gridTemplateColumns: responsive.statsGrid,
                  gap: responsive.gridGap,
                }"
              >
                <div
                  v-for="(stat, idx) in stats"
                  :key="idx"
                  :style="{
                    textAlign: 'left',
                    padding: isMobile ? '14px 2px 2px' : '18px 8px 4px',
                    background: 'transparent',
                    borderTop: `1px solid ${hexToRgba(designSettings.accentColor, 0.4)}`,
                    transition: 'all 0.3s',
                    cursor: 'pointer',
                  }"
                  @mouseover="(e) => handleHoverTransform(e, 'translateY(-3px)')"
                  @mouseout="(e) => handleHoverTransform(e, 'translateY(0)')"
                >
                  <div
                    :style="{
                      fontFamily: responsive.displayFont,
                      fontSize: isMobile ? '34px' : '48px',
                      fontWeight: 500,
                      lineHeight: 1,
                      background: 'linear-gradient(135deg, #e8d9b8, #c2a068)',
                      WebkitBackgroundClip: 'text',
                      WebkitTextFillColor: 'transparent',
                      marginBottom: '10px',
                    }"
                  >
                    {{ stat.num }}
                  </div>
                  <div
                    :style="{
                      color: responsive.textMuted,
                      fontSize: responsive.smallSize,
                      fontWeight: 500,
                      letterSpacing: '0.04em',
                      textTransform: 'uppercase',
                    }"
                  >
                    {{ stat.label }}
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Education Sub-section -->
            <div
              v-if="showEducationSection"
              :style="{
                marginBottom: showAwardsSection ? responsive.subSectionGap : '0',
                paddingTop: hasAboutData ? responsive.subSectionGap : '0',
                borderTop: hasAboutData ? `1px solid ${hexToRgba(designSettings.accentColor, 0.14)}` : 'none',
              }"
            >
              <div
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '12px',
                  marginBottom: '20px',
                }"
              >
                <div
                  :style="{
                    width: isMobile ? '32px' : '40px',
                    height: isMobile ? '32px' : '40px',
                    background: 'transparent',
                    border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                    borderRadius: '11px',
                    color: designSettings.accentColor,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '16px' : '20px',
                  }"
                >
                  <Icon name="heroicons:academic-cap" :style="{ width: isMobile ? '16px' : '20px', height: isMobile ? '16px' : '20px' }" aria-hidden="true" />
                </div>
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 600,
                    letterSpacing: '-0.01em',
                    color: responsive.textPrimary,
                    margin: 0,
                  }"
                >
                  Education & Certifications
                </h3>
              </div>
              
              <!-- Education List -->
              <div v-if="profile.education?.length > 0 && isFieldVisible('profileAchievements', 'education')" :style="{ marginBottom: profile.certifications?.length > 0 ? '24px' : '0' }">
                <div :style="{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }">
                  <Icon name="heroicons:book-open" :style="{ width: '18px', height: '18px' }" aria-hidden="true" />
                  <h4 :style="{ color: responsive.textPrimary, fontSize: responsive.h4Size, margin: 0, fontWeight: 600 }">Education</h4>
                </div>
                <div :style="{ display: 'grid', gap: '16px', position: 'relative', paddingLeft: isMobile ? '0' : '20px' }">
                  <!-- Timeline line -->
                  <div v-if="!isMobile" :style="{ position: 'absolute', left: '6px', top: '8px', bottom: '8px', width: '2px', background: 'linear-gradient(180deg, #a9854d, #c2a068)', borderRadius: '2px' }" />
                  <div
                    v-for="(edu, idx) in profile.education"
                    :key="idx"
                    :style="{
                      position: 'relative',
                      padding: isMobile ? '16px' : '20px',
                      background: 'linear-gradient(135deg, rgba(169, 133, 77, 0.1), rgba(194, 160, 104, 0.05))',
                      borderRadius: '16px',
                      border: '1px solid rgba(169, 133, 77, 0.2)',
                      transition: 'all 0.3s ease',
                    }"
                    @mouseover="(e) => { e.currentTarget.style.transform = 'translateX(5px)'; e.currentTarget.style.borderColor = 'rgba(169, 133, 77, 0.4)'; }"
                    @mouseout="(e) => { e.currentTarget.style.transform = 'translateX(0)'; e.currentTarget.style.borderColor = 'rgba(169, 133, 77, 0.2)'; }"
                  >
                    <!-- Timeline dot -->
                    <div v-if="!isMobile" :style="{ position: 'absolute', left: '-26px', top: '24px', width: '12px', height: '12px', background: 'linear-gradient(135deg, #a9854d, #c2a068)', borderRadius: '50%', border: '2px solid rgba(6, 9, 13, 0.9)' }" />
                    <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '8px' }">
                      <div :style="{ flex: 1, minWidth: 0 }">
                        <p :style="{ color: responsive.textPrimary, fontSize: responsive.bodySize, fontWeight: 700, marginBottom: '6px', overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ edu.degree || edu.name }}</p>
                        <p v-if="edu.institution" :style="{ color: responsive.textMuted, fontSize: responsive.smallSize, display: 'flex', alignItems: 'center', gap: '6px', overflowWrap: 'break-word', wordBreak: 'break-word' }">
                          <Icon name="heroicons:building-library" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> {{ edu.institution }}
                        </p>
                      </div>
                      <span v-if="edu.year" :style="{ padding: '4px 12px', background: 'rgba(169, 133, 77, 0.2)', borderRadius: '20px', color: chipTextColor, fontSize: responsive.smallSize, fontWeight: 600, whiteSpace: 'nowrap' }">{{ edu.year }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Certifications List -->
              <div v-if="profile.certifications?.length > 0 && isFieldVisible('profileAchievements', 'certifications')">
                <div :style="{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }">
                  <Icon name="heroicons:document-check" :style="{ width: '18px', height: '18px' }" aria-hidden="true" />
                  <h4 :style="{ color: responsive.textPrimary, fontSize: responsive.h4Size, margin: 0, fontWeight: 600 }">Certifications</h4>
                </div>
                <div :style="{ display: 'grid', gridTemplateColumns: isMobile ? '1fr' : 'repeat(2, 1fr)', gap: '16px' }">
                  <div
                    v-for="(cert, idx) in profile.certifications"
                    :key="idx"
                    :style="{
                      padding: isMobile ? '16px' : '20px',
                      background: 'linear-gradient(135deg, rgba(194, 160, 104, 0.1), rgba(169, 133, 77, 0.05))',
                      borderRadius: '16px',
                      border: '1px solid rgba(194, 160, 104, 0.2)',
                      transition: 'all 0.3s ease',
                      display: 'flex',
                      gap: '12px',
                      alignItems: 'flex-start',
                    }"
                    @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-3px)'; e.currentTarget.style.boxShadow = '0 10px 30px rgba(194, 160, 104, 0.2)'; }"
                    @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; }"
                  >
                    <div :style="{ width: '40px', height: '40px', background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`, border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`, borderRadius: '12px', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }">
                      <Icon name="heroicons:check-badge" :style="{ width: '20px', height: '20px', color: designSettings.accentColor }" aria-hidden="true" />
                    </div>
                    <div :style="{ flex: 1, minWidth: 0 }">
                      <p :style="{ color: responsive.textPrimary, fontSize: responsive.bodySize, fontWeight: 700, marginBottom: '4px', overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ cert.name }}</p>
                      <p v-if="cert.issuer" :style="{ color: responsive.textMuted, fontSize: responsive.smallSize, marginBottom: '4px', overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ cert.issuer }}</p>
                      <span v-if="cert.year" :style="{ color: responsive.textSubtle, fontSize: responsive.smallSize }">{{ cert.year }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Awards Sub-section -->
            <div
              v-if="showAwardsSection"
              :style="{
                paddingTop: (hasAboutData || showEducationSection) ? responsive.subSectionGap : '0',
                borderTop: (hasAboutData || showEducationSection) ? `1px solid ${hexToRgba(designSettings.accentColor, 0.14)}` : 'none',
              }"
            >
              <div
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '12px',
                  marginBottom: '20px',
                }"
              >
                <div
                  :style="{
                    width: isMobile ? '32px' : '40px',
                    height: isMobile ? '32px' : '40px',
                    background: 'transparent',
                    border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                    borderRadius: '11px',
                    color: designSettings.accentColor,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '16px' : '20px',
                  }"
                >
                  <Icon name="heroicons:trophy" :style="{ width: isMobile ? '16px' : '20px', height: isMobile ? '16px' : '20px' }" aria-hidden="true" />
                </div>
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 600,
                    letterSpacing: '-0.01em',
                    color: responsive.textPrimary,
                    margin: 0,
                  }"
                >
                  Awards & Recognition
                </h3>
              </div>
              <div :style="{ display: 'grid', gridTemplateColumns: isMobile ? '1fr' : 'repeat(2, 1fr)', gap: '16px' }">
                <div
                  v-for="(award, idx) in awards"
                  :key="idx"
                  :style="{
                    position: 'relative',
                    padding: isMobile ? '20px' : '24px',
                    background: 'linear-gradient(135deg, rgba(194, 160, 104, 0.1), rgba(232, 217, 184, 0.05))',
                    borderRadius: '20px',
                    border: '1px solid rgba(194, 160, 104, 0.2)',
                    transition: 'all 0.3s ease',
                    overflow: 'hidden',
                  }"
                  @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-5px)'; e.currentTarget.style.boxShadow = '0 15px 40px rgba(194, 160, 104, 0.15)'; e.currentTarget.style.borderColor = 'rgba(194, 160, 104, 0.4)'; }"
                  @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; e.currentTarget.style.borderColor = 'rgba(194, 160, 104, 0.2)'; }"
                >
                  <!-- Trophy decoration -->
                  <div :style="{ position: 'absolute', top: '-10px', right: '-10px', fontSize: '60px', opacity: 0.1, transform: 'rotate(15deg)' }"><Icon name="heroicons:trophy" :style="{ width: '60px', height: '60px' }" aria-hidden="true" /></div>
                  <div :style="{ position: 'relative', zIndex: 1 }">
                    <div :style="{ display: 'flex', alignItems: 'flex-start', gap: '14px' }">
                      <div :style="{ width: '50px', height: '50px', background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`, border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`, borderRadius: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }">
                        <Icon name="heroicons:trophy" :style="{ width: '24px', height: '24px', color: designSettings.accentColor }" aria-hidden="true" />
                      </div>
                      <div :style="{ flex: 1, minWidth: 0 }">
                        <p :style="{ color: '#c2a068', fontSize: responsive.bodySize, fontWeight: 700, marginBottom: '6px', textShadow: '0 0 20px rgba(194, 160, 104, 0.3)', overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ award.title || award.name }}</p>
                        <p v-if="award.organization" :style="{ color: responsive.textMuted, fontSize: responsive.smallSize, marginBottom: '4px', display: 'flex', alignItems: 'center', gap: '6px', overflowWrap: 'break-word', wordBreak: 'break-word' }">
                          <Icon name="heroicons:building-library" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> {{ award.organization }}
                        </p>
                        <span v-if="award.year" :style="{ display: 'inline-block', marginTop: '8px', padding: '4px 12px', background: 'rgba(194, 160, 104, 0.2)', borderRadius: '20px', color: '#c2a068', fontSize: responsive.smallSize, fontWeight: 600 }">{{ award.year }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Services -->
          <div
            v-if="showServicesSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:rocket-launch" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Services & Expertise
              </h2>
            </div>
            <div
              :style="{
                display: 'grid',
                gridTemplateColumns: responsive.servicesGrid,
                gap: responsive.gridGap,
              }"
            >
              <div
                v-for="(service, idx) in services"
                :key="idx"
                @mouseenter="!isMobile && (activeService = idx)"
                @mouseleave="!isMobile && (activeService = null)"
                @touchstart="activeService = idx"
                @touchend="setTimeout(() => (activeService = null), 2000)"
                @click="handleServiceClick(service)"
                :style="{
                  position: 'relative',
                  background: activeService === idx ? hexToRgba(designSettings.accentColor, 0.06) : 'rgba(255, 255, 255, 0.02)',
                  borderTop: `1px solid ${activeService === idx ? hexToRgba(designSettings.accentColor, 0.42) : hexToRgba(designSettings.accentColor, 0.16)}`,
                  borderRight: `1px solid ${activeService === idx ? hexToRgba(designSettings.accentColor, 0.42) : hexToRgba(designSettings.accentColor, 0.16)}`,
                  borderBottom: `1px solid ${activeService === idx ? hexToRgba(designSettings.accentColor, 0.42) : hexToRgba(designSettings.accentColor, 0.16)}`,
                  borderLeft: `1px solid ${activeService === idx ? hexToRgba(designSettings.accentColor, 0.42) : hexToRgba(designSettings.accentColor, 0.16)}`,
                  borderRadius: '16px',
                  padding: isMobile ? '25px 15px' : '30px 20px',
                  textAlign: 'center',
                  cursor: 'pointer',
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                  overflow: 'hidden',
                  transform:
                    activeService === idx
                      ? 'translateY(-6px)'
                      : 'translateY(0)',
                  boxShadow:
                    activeService === idx
                      ? `0 22px 48px -26px ${hexToRgba(designSettings.accentColor, 0.8)}`
                      : 'none',
                }"
              >
                <div
                  :style="{
                    width: responsive.serviceIconBox,
                    height: responsive.serviceIconBox,
                    margin: '0 auto 20px',
                    background: 'transparent',
                    border: `1px solid ${hexToRgba(designSettings.accentColor, 0.42)}`,
                    borderRadius: '14px',
                    color: designSettings.accentColor,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    transition: 'all 0.3s',
                    transform:
                      activeService === idx
                        ? 'scale(1.08)'
                        : 'scale(1)',
                  }"
                >
                  <Icon
                    :name="resolveServiceIcon(service.icon)"
                    :style="{ width: responsive.serviceIconSize, height: responsive.serviceIconSize }"
                    aria-hidden="true"
                  />
                </div>
                <h4
                  :style="{
                    color: responsive.textPrimary,
                    fontSize: responsive.bodySize,
                    fontWeight: 600,
                    margin: 0,
                    whiteSpace: 'normal',
                    overflowWrap: 'break-word',
                  }"
                >
                  {{ service.name }}
                </h4>
                <div
                  :style="{
                    position: 'absolute',
                    inset: 0,
                    background: 'rgba(6, 9, 13, 0.84)',
                    backdropFilter: 'blur(4px)',
                    WebkitBackdropFilter: 'blur(4px)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    opacity: activeService === idx ? 1 : 0,
                    transition: 'opacity 0.3s',
                  }"
                >
                  <span
                    :style="{
                      color: chipTextColor,
                      fontSize: responsive.bodySize,
                      fontWeight: 600,
                      letterSpacing: '0.02em',
                    }"
                    >{{ service.linkType === 'Direct Link' && service.url ? 'Visit' : 'Explore' }} <Icon name="heroicons:arrow-right" class="w-4 h-4 inline-block align-[-0.2em]" aria-hidden="true" /></span>
                </div>
              </div>
            </div>
            
            <!-- Service Details (Features, Price, etc.) -->
            <div
              v-if="serviceDetails.features.length > 0 || serviceDetails.price || serviceDetails.tags.length > 0 || serviceDetails.bookingEnabled"
              :style="{
                marginTop: responsive.gridGap,
                padding: responsive.cardPadding,
                background: 'rgba(194, 160, 104, 0.1)',
                borderRadius: responsive.cardRadius,
                border: '1px solid rgba(194, 160, 104, 0.2)',
              }"
            >
              <h3
                :style="{
                  fontSize: responsive.h3Size,
                  fontWeight: 600,
                  letterSpacing: '-0.01em',
                  color: responsive.textPrimary,
                  marginBottom: '20px',
                }"
              >
                Service Details
              </h3>
              
              <!-- Price & Duration -->
              <div
                v-if="(serviceDetails.price && isFieldVisible('services', 'servicePrice')) || (serviceDetails.duration && isFieldVisible('services', 'serviceDuration'))"
                :style="{
                  display: 'flex',
                  gap: '20px',
                  marginBottom: '20px',
                  flexWrap: 'wrap',
                }"
              >
                <div v-if="serviceDetails.price && isFieldVisible('services', 'servicePrice')" :style="{ display: 'flex', alignItems: 'baseline', gap: '10px' }">
                  <span :style="{ fontSize: responsive.h2Size, fontWeight: 800, color: '#c2a068' }">
                    ${{ serviceDetails.price }}
                  </span>
                  <span
                    v-if="serviceDetails.oldPrice"
                    :style="{
                      fontSize: responsive.bodySize,
                      color: 'rgba(255, 255, 255, 0.4)',
                      textDecoration: 'line-through',
                    }"
                  >
                    ${{ serviceDetails.oldPrice }}
                  </span>
                </div>
                <div
                  v-if="serviceDetails.duration && isFieldVisible('services', 'serviceDuration')"
                  :style="{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px',
                    padding: '8px 16px',
                    background: 'rgba(255, 255, 255, 0.1)',
                    borderRadius: '20px',
                    color: responsive.textMuted,
                    fontSize: responsive.bodySize,
                  }"
                >
                  <Icon name="heroicons:clock" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> {{ serviceDetails.duration }}
                </div>
              </div>
              
              <!-- Features -->
              <div v-if="serviceDetails.features.length > 0 && isFieldVisible('services', 'serviceFeatures')" :style="{ marginBottom: '20px' }">
                <h4 :style="{ fontSize: responsive.h4Size, color: responsive.textPrimary, marginBottom: '12px' }">Features:</h4>
                <ul :style="{ listStyle: 'none', padding: 0, margin: 0 }">
                  <li
                    v-for="(feature, idx) in serviceDetails.features"
                    :key="idx"
                    :style="{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '10px',
                      padding: '8px 0',
                      color: responsive.textMuted,
                      fontSize: responsive.bodySize,
                    }"
                  >
                    <span :style="{ color: '#c2a068' }"><Icon name="heroicons:check" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /></span> {{ typeof feature === 'object' ? (feature.feature || feature.name || feature) : feature }}
                  </li>
                </ul>
              </div>
              
              <!-- Tags -->
              <div v-if="serviceDetails.tags.length > 0 && isFieldVisible('services', 'serviceTags')" :style="{ marginBottom: '20px' }">
                <div :style="{ display: 'flex', gap: '8px', flexWrap: 'wrap' }">
                  <span
                    v-for="(tag, idx) in serviceDetails.tags"
                    :key="idx"
                    :style="{
                      padding: '6px 14px',
                      background: 'rgba(194, 160, 104, 0.3)',
                      border: '1px solid rgba(194, 160, 104, 0.5)',
                      borderRadius: '16px',
                      fontSize: responsive.smallSize,
                      color: chipTextColor,
                    }"
                  >
                    #{{ typeof tag === 'object' ? (tag.tag || tag.name || tag) : tag }}
                  </span>
                </div>
              </div>
              
              <!-- Action Buttons -->
              <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap' }">
                <!-- Brochure Download -->
                <a
                  v-if="serviceDetails.brochure && isFieldVisible('services', 'serviceBrochure')"
                  :href="serviceDetails.brochure"
                  download
                  :style="{
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '8px',
                    padding: '12px 24px',
                    background: 'transparent',
                    border: `1px solid ${hexToRgba(designSettings.accentColor, 0.32)}`,
                    borderRadius: '999px',
                    color: responsive.textPrimary,
                    fontSize: responsive.bodySize,
                    fontWeight: 600,
                    textDecoration: 'none',
                    transition: 'all 0.3s',
                  }"
                  @mouseover="(e) => { e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.1); e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.6); }"
                  @mouseout="(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.32); }"
                >
                  <Icon name="heroicons:document-text" class="w-4 h-4 flex-shrink-0" :style="{ color: designSettings.accentColor }" aria-hidden="true" /> Download Brochure
                </a>
                
                <!-- Booking Button -->
                <a
                  v-if="serviceDetails.bookingEnabled && serviceDetails.bookingUrl && isFieldVisible('services', 'bookingUrl')"
                  :href="serviceDetails.bookingUrl"
                  target="_blank"
                  :style="{
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '8px',
                    padding: '12px 24px',
                    background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
                    borderRadius: '999px',
                    color: '#1b1407',
                    fontSize: responsive.bodySize,
                    fontWeight: 700,
                    textDecoration: 'none',
                    transition: 'all 0.3s',
                    boxShadow: '0 10px 26px -12px rgba(194, 160, 104, 0.7)',
                  }"
                  @mouseover="(e) => e.currentTarget.style.transform = 'translateY(-2px)'"
                  @mouseout="(e) => e.currentTarget.style.transform = 'translateY(0)'"
                >
                  <Icon name="heroicons:calendar" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> Book Now
                </a>
              </div>
            </div>
          </div>

          <!-- Expertise & Skills -->
          <div
            v-if="expertise.length > 0"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:light-bulb" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Expertise & Skills
              </h2>
            </div>
            <div :style="{ display: 'grid', gap: '15px' }">
              <div
                v-for="(skill, idx) in expertise"
                :key="idx"
                :style="{
                  padding: isMobile ? '15px 2px' : '18px 2px',
                  background: 'transparent',
                  borderRadius: 0,
                  borderBottom: `1px solid ${hexToRgba(designSettings.accentColor, 0.16)}`,
                }"
              >
                <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }">
                  <span :style="{ color: responsive.textPrimary, fontSize: responsive.bodySize, fontWeight: 600, letterSpacing: '-0.01em' }">{{ skill.name }}</span>
                  <span :style="{ color: designSettings.accentColor, fontSize: responsive.smallSize, fontWeight: 600 }">{{ skill.level }}%</span>
                </div>
                <div :style="{ width: '100%', height: '3px', background: 'rgba(255, 255, 255, 0.08)', borderRadius: '2px', overflow: 'hidden' }">
                  <div
                    :style="{
                      width: `${skill.level || 0}%`,
                      height: '100%',
                      background: 'linear-gradient(90deg, #c2a068, #e8d9b8)',
                      borderRadius: '2px',
                      transition: 'width 0.5s ease-out',
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact -->
          <div
            v-if="showContactSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:phone" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Get In Touch
              </h2>
            </div>
            <div :style="{ display: 'grid', gap: responsive.gridGap }">
              <a
                v-for="(contact, idx) in filteredContactMethods"
                :key="idx"
                :href="contact.href || '#'"
                :target="contact.href && contact.href.startsWith('http') ? '_blank' : '_top'"
                :rel="
                  contact.href && contact.href.startsWith('http')
                    ? 'noopener noreferrer'
                    : undefined
                "
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: isSmallMobile ? '14px' : isMobile ? '16px' : '20px',
                  padding: isSmallMobile ? '16px 6px' : isMobile ? '18px 8px' : '22px 10px',
                  background: 'transparent',
                  borderBottom: `1px solid ${hexToRgba(designSettings.accentColor, 0.16)}`,
                  borderRadius: 0,
                  textDecoration: 'none',
                  transition: 'all 0.3s',
                  animation: 'slideInRight 0.6s ease-out backwards',
                  animationDelay: `${idx * 0.1}s`,
                }"
                @mouseover="handleContactHoverIn"
                @mouseout="handleContactHoverOut"
              >
                <div
                  :style="{
                    width: responsive.contactIconBox,
                    height: responsive.contactIconBox,
                    borderRadius: '12px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    flexShrink: 0,
                    background: hexToRgba(designSettings.accentColor, 0.10),
                    border: `1px solid ${hexToRgba(designSettings.accentColor, 0.42)}`,
                  }"
                >
                  <svg
                    :style="{
                      width: responsive.contactIconSize,
                      height: responsive.contactIconSize,
                      fill: designSettings.accentColor,
                    }"
                    viewBox="0 0 24 24"
                  >
                    <path :d="contact.icon" />
                  </svg>
                </div>
                <div :style="{ flex: 1, minWidth: 0 }">
                  <h4
                    :style="{
                      color: responsive.textPrimary,
                      fontSize: responsive.h4Size,
                      fontWeight: 700,
                      margin: isSmallMobile ? '0 0 3px 0' : '0 0 5px 0',
                      whiteSpace: 'normal',
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                      lineHeight: 1.3,
                    }"
                  >
                    {{ contact.label }}
                  </h4>
                  <p
                    :style="{
                      color: responsive.textMuted,
                      fontSize: responsive.smallSize,
                      margin: 0,
                      whiteSpace: 'normal',
                      overflowWrap: 'break-word',
                      wordBreak: 'break-word',
                      lineHeight: 1.45,
                    }"
                  >
                    {{ contact.subtitle }}
                  </p>
                </div>
                <div
                  v-if="!isMobile"
                  :style="{
                    color: 'rgba(255, 255, 255, 0.4)',
                    fontSize: '24px',
                    transition: 'all 0.3s',
                    flexShrink: 0,
                  }"
                >
                  <Icon name="heroicons:arrow-right" class="w-6 h-6" aria-hidden="true" />
                </div>
              </a>
            </div>
          </div>

          <!-- Location -->
          <div
            v-if="showLocationSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:map-pin" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Our Location
              </h2>
            </div>
            <div
              :style="{
                padding: isMobile ? '20px' : '25px',
                background: 'transparent',
                borderRadius: '16px',
                border: `1px solid ${hexToRgba(designSettings.accentColor, 0.16)}`,
                marginBottom: isMobile ? '20px' : '25px',
              }"
            >
              <h4
                v-if="address.name && isFieldVisible('location', 'addressName')"
                :style="{
                  color: responsive.textPrimary,
                  fontSize: responsive.h4Size,
                  fontWeight: 600,
                  letterSpacing: '-0.01em',
                  marginBottom: '15px',
                  overflowWrap: 'break-word',
                  wordBreak: 'break-word',
                }"
              >
                {{ address.name }}
              </h4>
              <p
                v-if="addressDisplayLines.length > 0"
                :style="{
                  color: responsive.textMuted,
                  lineHeight: '1.8',
                  fontSize: responsive.bodySize,
                  overflowWrap: 'break-word',
                  wordBreak: 'break-word',
                }"
              >
                <template v-for="(line, idx) in addressDisplayLines" :key="idx">
                  <template v-if="idx > 0"><br /></template>
                  {{ line }}
                </template>
              </p>
            </div>
            <a
              v-if="address.mapUrl && isFieldVisible('location', 'mapUrl')"
              :href="address.mapUrl"
              target="_blank"
              rel="noopener noreferrer"
              :style="{
                position: 'relative',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '10px',
                padding: isMobile ? '15px' : '18px',
                background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
                color: '#1b1407',
                textDecoration: 'none',
                borderRadius: '14px',
                fontWeight: 700,
                fontSize: responsive.bodySize,
                transition: 'all 0.3s',
                overflow: 'hidden',
                boxShadow: '0 16px 34px -16px rgba(194, 160, 104, 0.7)',
              }"
              @mouseover="handleMapButtonHoverIn"
              @mouseout="handleMapButtonHoverOut"
            >
              <Icon name="heroicons:map" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              Open Maps
            </a>
          </div>

          <!-- Social Media -->
          <div
            v-if="showSocialSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:globe-alt" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Connect With Me
              </h2>
            </div>
            <div
              :style="{
                display: 'grid',
                gridTemplateColumns: isSmallMobile
                  ? '1fr'
                  : isMobile
                    ? 'repeat(2, 1fr)'
                    : 'repeat(auto-fit, minmax(150px, 1fr))',
                gap: isSmallMobile ? '10px' : '15px',
              }"
            >
              <a
                v-for="(social, idx) in socialLinks"
                :key="idx"
                :href="social.href"
                :target="linkTarget"
                :rel="linkTarget === '_blank' ? 'noopener noreferrer' : undefined"
                @click="trackLinkClick(social)"
                :style="{
                  position: 'relative',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: isSmallMobile ? '8px' : '10px',
                  padding: isSmallMobile
                    ? '12px 10px'
                    : isMobile
                      ? '15px 10px'
                      : '18px',
                  background: 'rgba(255, 255, 255, 0.05)',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  borderRadius: isSmallMobile ? '12px' : '15px',
                  color: responsive.textPrimary,
                  textDecoration: 'none',
                  fontWeight: 600,
                  fontSize: isMobile
                    ? responsive.smallSize
                    : responsive.bodySize,
                  transition: 'all 0.3s',
                  overflow: 'hidden',
                  overflowWrap: 'break-word',
                  textAlign: 'center',
                  minWidth: 0,
                }"
                @mouseover="(e) => handleSocialHoverIn(e, social.color)"
                @mouseout="handleSocialHoverOut"
              >
                <Icon
                  :name="social.emoji"
                  :style="{
                    width: isSmallMobile ? '18px' : isMobile ? '20px' : '24px',
                    height: isSmallMobile ? '18px' : isMobile ? '20px' : '24px',
                    flexShrink: 0,
                  }"
                  aria-hidden="true"
                />
                <span
                  :style="{
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    minWidth: 0,
                  }"
                >{{ social.name }}</span>
              </a>
            </div>
          </div>

          <!-- Working Hours -->
          <div
            v-if="workingHours.length > 0"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:clock" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Working Hours
              </h2>
            </div>
            <div :style="{ display: 'grid', gap: '10px' }">
              <div
                v-for="(hour, idx) in workingHours"
                :key="idx"
                :style="{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  padding: '15px',
                  background: 'rgba(255, 255, 255, 0.05)',
                  borderRadius: '10px',
                }"
              >
                <span :style="{ color: responsive.textPrimary, fontSize: responsive.bodySize, fontWeight: 600 }">{{ hour.day }}</span>
                <span :style="{ color: responsive.textMuted, fontSize: responsive.bodySize }">{{ hour.hours || 'Closed' }}</span>
              </div>
            </div>
          </div>

          <!-- Gallery -->
          <div
            v-if="showGallerySection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:photo" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Gallery
              </h2>
            </div>
            <div
              :style="{
                display: 'grid',
                gridTemplateColumns: responsive.galleryGrid,
                gap: responsive.gridGap,
              }"
            >
              <div
                v-for="(image, idx) in gallery"
                :key="idx"
                :style="{
                  position: 'relative',
                  paddingBottom: '100%',
                  borderRadius: '15px',
                  overflow: 'hidden',
                  background: 'rgba(255, 255, 255, 0.05)',
                }"
              >
                <img
                  :src="image.url || image"
                  :alt="image.caption || `Gallery image ${idx + 1}`"
                  :style="{
                    position: 'absolute',
                    inset: 0,
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover',
                  }"
                />
              </div>
            </div>
          </div>

          <!-- Portfolio -->
          <div
            v-if="showPortfolioSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:briefcase" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Portfolio
              </h2>
            </div>
            <div :style="{ display: 'grid', gridTemplateColumns: isMobile ? '1fr' : 'repeat(2, 1fr)', gap: '24px' }">
              <div
                v-for="(project, idx) in portfolio"
                :key="idx"
                @click="openPortfolioModal(project)"
                :style="{
                  background: 'rgba(255, 255, 255, 0.02)',
                  borderRadius: '18px',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.16)}`,
                  overflow: 'hidden',
                  transition: 'all 0.4s ease',
                  cursor: 'pointer',
                }"
                @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-6px)'; e.currentTarget.style.boxShadow = `0 24px 54px -28px ${hexToRgba(designSettings.accentColor, 0.6)}`; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.4); }"
                @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.16); }"
              >
                <!-- Image with overlay -->
                <div
                  v-if="(project.cover_image || project.image) && isFieldVisible('portfolio', 'portfolioCoverImage')"
                  :style="{
                    position: 'relative',
                    width: '100%',
                    height: isMobile ? '180px' : '220px',
                    overflow: 'hidden',
                  }"
                >
                  <img
                    :src="project.cover_image || project.image"
                    :alt="project.title"
                    :style="{
                      width: '100%',
                      height: '100%',
                      objectFit: 'cover',
                      transition: 'transform 0.4s ease',
                    }"
                  />
                  <div :style="{ position: 'absolute', inset: 0, background: 'linear-gradient(to top, rgba(6, 9, 13, 0.9) 0%, transparent 60%)' }" />
                  <!-- Project number badge -->
                  <div :style="{ position: 'absolute', top: '12px', left: '12px', width: '40px', height: '40px', background: 'rgba(6, 9, 13, 0.55)', backdropFilter: 'blur(6px)', WebkitBackdropFilter: 'blur(6px)', border: `1px solid ${hexToRgba(designSettings.accentColor, 0.55)}`, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: chipTextColor, fontFamily: responsive.displayFont, fontWeight: 500, fontSize: '15px' }">
                    {{ String(idx + 1).padStart(2, '0') }}
                  </div>
                </div>
                
                <!-- Content -->
                <div :style="{ padding: isMobile ? '20px' : '24px' }">
                  <h3 :style="{ color: responsive.textPrimary, fontSize: responsive.h3Size, fontWeight: 600, letterSpacing: '-0.01em', marginBottom: '12px', lineHeight: 1.3, overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ project.title }}</h3>
                  
                  <!-- Project Meta Info -->
                  <div
                    v-if="project.client_name || project.date_completed || project.location"
                    :style="{
                      display: 'flex',
                      gap: '16px',
                      flexWrap: 'wrap',
                      marginBottom: '14px',
                    }"
                  >
                    <span
                      v-if="project.client_name && isFieldVisible('portfolio', 'clientName')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '6px',
                        padding: '6px 12px',
                        background: 'transparent',
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.22)}`,
                        borderRadius: '20px',
                        fontSize: responsive.smallSize,
                        color: responsive.textMuted,
                        maxWidth: '100%',
                        overflowWrap: 'break-word',
                        wordBreak: 'break-word',
                      }"
                    >
                      <Icon name="heroicons:user" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />{{ project.client_name }}
                    </span>
                    <span
                      v-if="project.date_completed && isFieldVisible('portfolio', 'dateCompleted')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '6px',
                        padding: '6px 12px',
                        background: 'transparent',
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.22)}`,
                        borderRadius: '20px',
                        fontSize: responsive.smallSize,
                        color: responsive.textMuted,
                        maxWidth: '100%',
                        overflowWrap: 'break-word',
                        wordBreak: 'break-word',
                      }"
                    >
                      <Icon name="heroicons:calendar" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />{{ project.date_completed }}
                    </span>
                    <span
                      v-if="project.location && isFieldVisible('portfolio', 'location')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '6px',
                        padding: '6px 12px',
                        background: 'transparent',
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.22)}`,
                        borderRadius: '20px',
                        fontSize: responsive.smallSize,
                        color: responsive.textMuted,
                        maxWidth: '100%',
                        overflowWrap: 'break-word',
                        wordBreak: 'break-word',
                      }"
                    >
                      <Icon name="heroicons:map-pin" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />{{ project.location }}
                    </span>
                  </div>
                  
                  <p v-if="project.description" :style="{ color: responsive.textMuted, fontSize: responsive.bodySize, lineHeight: '1.7', marginBottom: '16px', display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden' }" v-html="project.description" />
                
                <!-- Skills Used -->
                <div
                  v-if="project.skills_used && project.skills_used.length > 0 && isFieldVisible('portfolio', 'skillsUsed')"
                  :style="{ marginBottom: '15px' }"
>
                  <p :style="{ color: responsive.textSubtle, fontSize: responsive.smallSize, marginBottom: '8px' }">Skills:</p>
                  <div :style="{ display: 'flex', gap: '8px', flexWrap: 'wrap' }">
                    <span
                      v-for="(skill, skillIdx) in project.skills_used"
                      :key="skillIdx"
                      :style="{
                        padding: '4px 10px',
                        background: 'rgba(169, 133, 77, 0.2)',
                        border: '1px solid rgba(169, 133, 77, 0.3)',
                        borderRadius: '12px',
                        color: chipTextColor,
                        fontSize: responsive.smallSize,
                      }"
                    >{{ typeof skill === 'object' ? (skill.skill || skill.name || skill) : skill }}</span>
                  </div>
                </div>
                
                <!-- Tags -->
                <div v-if="project.tags && project.tags.length > 0" :style="{ display: 'flex', gap: '8px', flexWrap: 'wrap', marginBottom: '15px' }">
                  <span
                    v-for="(tag, tagIdx) in project.tags"
                    :key="tagIdx"
                    :style="{
                      padding: '6px 12px',
                      background: 'rgba(194, 160, 104, 0.2)',
                      border: '1px solid rgba(194, 160, 104, 0.4)',
                      borderRadius: '50px',
                      color: chipTextColor,
                      fontSize: responsive.smallSize,
                      fontWeight: 600,
                    }"
                  >{{ typeof tag === 'object' ? (tag.tag || tag.name || tag) : tag }}</span>
                </div>
                
                  <!-- Action Buttons -->
                  <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap', marginTop: '16px' }">
                    <button
                      @click.stop="openPortfolioModal(project)"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '8px',
                        padding: '12px 20px',
                        background: 'transparent',
                        borderRadius: '999px',
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`,
                        color: responsive.textPrimary,
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        cursor: 'pointer',
                        transition: 'all 0.3s',
                      }"
                      @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.1); e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.7); }"
                      @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.background = 'transparent'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.45); }"
                    >
                      <Icon name="heroicons:link" class="w-4 h-4 flex-shrink-0" :style="{ color: designSettings.accentColor }" aria-hidden="true" /> View Project
                    </button>
                    <a
                      v-if="project.pdf_download && isFieldVisible('portfolio', 'pdfDownload')"
                      :href="project.pdf_download"
                      download
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '8px',
                        padding: '12px 20px',
                        background: 'transparent',
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.3)}`,
                        borderRadius: '999px',
                        color: responsive.textPrimary,
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        textDecoration: 'none',
                        transition: 'all 0.3s',
                      }"
                      @mouseover="(e) => { e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.1); e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.55); }"
                      @mouseout="(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.3); }"
                    >
                      <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> Download PDF
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Blog Posts -->
          <div
            v-if="showBlogSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: `blur(${responsive.cardBackdropBlur})`,
              WebkitBackdropFilter: `blur(${responsive.cardBackdropBlur})`,
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                marginBottom: isMobile ? '22px' : '32px',
                paddingBottom: isMobile ? '14px' : '18px',
                borderBottom: '1px solid rgba(194, 160, 104, 0.14)',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '38px' : '46px',
                  height: isMobile ? '38px' : '46px',
                  background: `linear-gradient(160deg, ${hexToRgba(designSettings.accentColor, 0.24)} 0%, rgba(10, 14, 20, 0.55) 100%)`,
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.5)}`,
                  borderRadius: '13px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  boxShadow: 'inset 0 1px 0 rgba(255, 255, 255, 0.07)',
                }"
              >
                <Icon name="heroicons:document-text" :class="isMobile ? 'w-5 h-5' : 'w-6 h-6'" aria-hidden="true" />
              </div>
              <h2
                :style="{
                  fontFamily: responsive.displayFont,
                  fontSize: responsive.h2Size,
                  fontWeight: 600,
                  letterSpacing: '-0.015em',
                  lineHeight: 1.1,
                  color: responsive.textPrimary,
                  margin: 0,
                }"
              >
                Blog
              </h2>
            </div>
            <div :style="{ display: 'grid', gridTemplateColumns: isMobile ? '1fr' : 'repeat(2, 1fr)', gap: '24px' }">
              <article
                v-for="(post, idx) in blogPosts"
                :key="idx"
                :style="{
                  background: 'linear-gradient(135deg, rgba(232, 217, 184, 0.08), rgba(138, 106, 51, 0.05))',
                  borderRadius: '24px',
                  border: '1px solid rgba(232, 217, 184, 0.15)',
                  overflow: 'hidden',
                  transition: 'all 0.4s ease',
                }"
                @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-8px)'; e.currentTarget.style.boxShadow = '0 20px 50px rgba(232, 217, 184, 0.15)'; e.currentTarget.style.borderColor = 'rgba(232, 217, 184, 0.3)'; }"
                @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; e.currentTarget.style.borderColor = 'rgba(232, 217, 184, 0.15)'; }"
              >
                <!-- Cover Image with overlay -->
                <div
                  v-if="((post.gallery && post.gallery.length > 0) || post.cover_image) && isFieldVisible('blog', 'blogCoverImage')"
                  :style="{
                    position: 'relative',
                    width: '100%',
                    height: isMobile ? '180px' : '200px',
                    overflow: 'hidden',
                  }"
                >
                  <img
                    :src="getGalleryImageUrl(post.gallery?.[0]) || post.cover_image"
                    :alt="post.title"
                    :style="{
                      width: '100%',
                      height: '100%',
                      objectFit: 'cover',
                      transition: 'transform 0.4s ease',
                    }"
                  />
                  <div :style="{ position: 'absolute', inset: 0, background: 'linear-gradient(to top, rgba(6, 9, 13, 0.95) 0%, transparent 70%)' }" />
                  <!-- Category badge on image -->
                  <span v-if="post.category && isFieldVisible('blog', 'blogCategory')" :style="{ position: 'absolute', top: '12px', left: '12px', padding: '6px 14px', background: 'rgba(10, 14, 20, 0.82)', backdropFilter: 'blur(8px)', WebkitBackdropFilter: 'blur(8px)', border: '1px solid rgba(194, 160, 104, 0.4)', borderRadius: '999px', color: chipTextColor, fontSize: responsive.smallSize, fontWeight: 600, letterSpacing: '0.04em' }">{{ post.category }}</span>
                </div>
                
                <!-- Content -->
                <div :style="{ padding: isMobile ? '20px' : '24px' }">
                  <!-- Blog Meta -->
                  <div :style="{ display: 'flex', gap: '12px', marginBottom: '12px', flexWrap: 'wrap', alignItems: 'center' }">
                    <span v-if="post.published_date" :style="{ display: 'flex', alignItems: 'center', gap: '6px', color: responsive.textMuted, fontSize: responsive.smallSize }"><Icon name="heroicons:calendar" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> {{ post.published_date }}</span>
                    <span v-if="post.reading_time && isFieldVisible('blog', 'readingTime')" :style="{ display: 'flex', alignItems: 'center', gap: '6px', padding: '4px 10px', background: 'rgba(255, 255, 255, 0.1)', borderRadius: '15px', color: responsive.textMuted, fontSize: responsive.smallSize }"><Icon name="heroicons:clock" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> {{ post.reading_time }}</span>
                  </div>
                  
                  <h3 :style="{ color: responsive.textPrimary, fontSize: responsive.h3Size, fontWeight: 700, marginBottom: '12px', lineHeight: 1.3, whiteSpace: 'normal', overflowWrap: 'break-word' }">{{ post.title }}</h3>
                  
                  <!-- Author -->
                  <div
                    v-if="post.author_name && isFieldVisible('blog', 'authorName')"
                    :style="{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '10px',
                      marginBottom: '14px',
                    }"
                  >
                    <div :style="{ width: '32px', height: '32px', background: hexToRgba(designSettings.accentColor, 0.16), border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center' }">
                      <Icon name="heroicons:pencil" :style="{ width: '14px', height: '14px', color: designSettings.accentColor }" aria-hidden="true" />
                    </div>
                    <span :style="{ color: responsive.textMuted, fontSize: responsive.smallSize, fontWeight: 500, minWidth: 0, overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ post.author_name }}</span>
                  </div>
                  
                  <p v-if="post.excerpt || post.description" :style="{ color: responsive.textMuted, fontSize: responsive.bodySize, lineHeight: '1.7', marginBottom: '16px', display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden' }" v-html="post.excerpt || post.description" />
                  
                  <!-- Tags -->
                  <div v-if="post.tags && post.tags.length > 0 && isFieldVisible('blog', 'blogTags')" :style="{ display: 'flex', gap: '8px', flexWrap: 'wrap', marginBottom: '16px' }">
                    <span
                      v-for="(tag, tagIdx) in post.tags"
                      :key="tagIdx"
                      :style="{
                        padding: '5px 12px',
                        background: 'rgba(232, 217, 184, 0.15)',
                        border: '1px solid rgba(232, 217, 184, 0.25)',
                        borderRadius: '15px',
                        color: chipTextColor,
                        fontSize: responsive.smallSize,
                        transition: 'all 0.2s',
                      }"
                    >#{{ typeof tag === 'object' ? (tag.tag || tag.name || tag) : tag }}</span>
                  </div>
                  
                  <!-- Buttons Row -->
                  <div :style="{ display: 'flex', gap: '10px', marginTop: '16px', flexWrap: 'wrap' }">
                    <!-- Read More (Modal) -->
                    <button
                      @click="openBlogModal(post)"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '8px',
                        padding: '10px 20px',
                        background: 'transparent',
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`,
                        borderRadius: '999px',
                        color: responsive.textPrimary,
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        cursor: 'pointer',
                        transition: 'all 0.3s',
                      }"
                      @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.1); e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.7); }"
                      @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.background = 'transparent'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.45); }"
                    >
                      <Icon name="heroicons:book-open" class="w-4 h-4 flex-shrink-0" :style="{ color: designSettings.accentColor }" aria-hidden="true" /> Read More
                    </button>

                    <!-- External Link (if available) -->
                    <a
                      v-if="post.external_link && isFieldVisible('blog', 'externalLink')"
                      :href="post.external_link"
                      target="_blank"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '8px',
                        padding: '10px 20px',
                        background: 'transparent',
                        border: `1px solid ${hexToRgba(designSettings.accentColor, 0.3)}`,
                        borderRadius: '999px',
                        color: responsive.textPrimary,
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        textDecoration: 'none',
                        transition: 'all 0.3s',
                      }"
                      @mouseover="(e) => { e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.1); e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.55); }"
                      @mouseout="(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.3); }"
                    >
                      <Icon name="heroicons:link" class="w-4 h-4 flex-shrink-0" :style="{ color: designSettings.accentColor }" aria-hidden="true" /> Visit Link
                    </a>
                  </div>
                </div>
              </article>
            </div>
          </div>

          <!-- Action Buttons Section -->
          <div v-if="hasQuickActions" :style="{ 
            background: 'transparent',
            borderRadius: 0,
            padding: `0 ${responsive.cardPadding}`,
            borderTop: `1px solid ${hexToRgba(designSettings.accentColor, 0.14)}`,
            paddingTop: isSmallMobile ? '22px' : '28px',
          }">
            <!-- Section Title -->
            <div :style="{ 
              display: 'flex', 
              alignItems: 'center', 
              gap: isSmallMobile ? '6px' : '8px', 
              marginBottom: isSmallMobile ? '12px' : '16px',
              paddingBottom: isSmallMobile ? '10px' : '12px',
              borderBottom: 'none',
            }">
              <Icon name="heroicons:bolt" :style="{ width: isSmallMobile ? '15px' : '17px', height: isSmallMobile ? '15px' : '17px', color: designSettings.accentColor }" aria-hidden="true" />
              <span :style="{ color: responsive.textMuted, fontSize: isSmallMobile ? '11px' : '12px', fontWeight: 600, letterSpacing: '0.16em', textTransform: 'uppercase' }">Quick Actions</span>
            </div>
            
            <!-- Buttons Grid -->
            <div :style="{ display: 'grid', gridTemplateColumns: isSmallMobile && quickActionCount > 4 ? '1fr' : 'repeat(2, 1fr)', gap: isSmallMobile ? '10px' : '12px' }">
              
              <!-- vCard Button -->
              <button
                v-if="isFeatureEnabled('vcard_download')"
                @click="saveContact"
                :style="{
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '10px',
                  padding: '20px 12px',
                  background: 'transparent',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.22)}`,
                  borderRadius: '14px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.07); e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.5); }"
                @mouseout="(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.22); }"
              >
                <div :style="{ width: '46px', height: '46px', background: 'transparent', border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: designSettings.accentColor }">
                  <Icon name="heroicons:arrow-down-tray" :style="{ width: '20px', height: '20px' }" aria-hidden="true" />
                </div>
                <span :style="{ color: responsive.textPrimary, fontSize: '13px', fontWeight: 600 }">Save Contact</span>
              </button>

              <!-- Contact Form Button -->
              <button
                v-if="isFeatureEnabled('contact_form')"
                @click="showContactForm = true"
                :style="{
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '10px',
                  padding: '20px 12px',
                  background: 'transparent',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.22)}`,
                  borderRadius: '14px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.07); e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.5); }"
                @mouseout="(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.22); }"
              >
                <div :style="{ width: '46px', height: '46px', background: 'transparent', border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: designSettings.accentColor }">
                  <Icon name="heroicons:envelope" :style="{ width: '20px', height: '20px' }" aria-hidden="true" />
                </div>
                <span :style="{ color: responsive.textPrimary, fontSize: '13px', fontWeight: 600 }">Message</span>
              </button>

              <!-- QR Code Button -->
              <button
                v-if="isFeatureEnabled('qr_code')"
                @click="showQRCode = true"
                :style="{
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '10px',
                  padding: '20px 12px',
                  background: 'transparent',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.22)}`,
                  borderRadius: '14px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.07); e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.5); }"
                @mouseout="(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.22); }"
              >
                <div :style="{ width: '46px', height: '46px', background: 'transparent', border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: designSettings.accentColor }">
                  <Icon name="heroicons:qr-code" :style="{ width: '20px', height: '20px' }" aria-hidden="true" />
                </div>
                <span :style="{ color: responsive.textPrimary, fontSize: '13px', fontWeight: 600 }">QR Code</span>
              </button>

              <!-- Social Sharing Button -->
              <button
                v-if="isFeatureEnabled('social_sharing')"
                @click="showSocialShare = true"
                :style="{
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '10px',
                  padding: '20px 12px',
                  background: 'transparent',
                  border: `1px solid ${hexToRgba(designSettings.accentColor, 0.22)}`,
                  borderRadius: '14px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.07); e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.5); }"
                @mouseout="(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = hexToRgba(designSettings.accentColor, 0.22); }"
              >
                <div :style="{ width: '46px', height: '46px', background: 'transparent', border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: designSettings.accentColor }">
                  <Icon name="heroicons:share" :style="{ width: '20px', height: '20px' }" aria-hidden="true" />
                </div>
                <span :style="{ color: responsive.textPrimary, fontSize: '13px', fontWeight: 600 }">Share</span>
              </button>
            </div>

            <!-- Booking Integration Button (Full Width) -->
            <a
              v-if="isFeatureEnabled('booking_integration') && appointmentLink"
              :href="appointmentLink"
              target="_blank"
              :style="{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '12px',
                marginTop: '12px',
                padding: '14px 20px',
                background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
                border: 'none',
                borderRadius: '14px',
                cursor: 'pointer',
                transition: 'all 0.3s',
                textDecoration: 'none',
                boxShadow: '0 12px 30px -14px rgba(194, 160, 104, 0.7)',
              }"
              @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.boxShadow = '0 16px 38px -14px rgba(194, 160, 104, 0.85)'; }"
              @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = '0 12px 30px -14px rgba(194, 160, 104, 0.7)'; }"
            >
              <Icon name="heroicons:calendar" :style="{ width: '18px', height: '18px', color: '#1b1407' }" aria-hidden="true" />
              <span :style="{ color: '#1b1407', fontSize: '14px', fontWeight: 700 }">Book Appointment</span>
            </a>

          </div>
        </div>

        <!-- ===================== NFCGo Signature Badge ===================== -->
        <!-- Subtle, plan-gated credit line. Free/Basic profiles display it; the
             plan + admin policy decides visibility (see show_nfcgo_branding). -->
        <div
          v-if="showNfcgoBranding"
          :style="{
            marginTop: responsive.sectionGap,
            paddingTop: isMobile ? '20px' : '24px',
            borderTop: '1px solid rgba(194, 160, 104, 0.16)',
            display: 'flex',
            justifyContent: 'center',
          }"
        >
          <NuxtLink
            to="/Homepage/cards"
            :style="{
              display: 'inline-flex',
              alignItems: 'center',
              gap: isSmallMobile ? '8px' : '10px',
              textDecoration: 'none',
              opacity: 0.92,
            }"
          >
            <NFCGoLogo variant="dark" :size="isSmallMobile ? 'xs' : 'sm'" />
            <span :style="{ color: responsive.textMuted, fontSize: isSmallMobile ? '11px' : '12px', letterSpacing: '0.04em' }">
              Powered by NFCGo
            </span>
          </NuxtLink>
        </div>
      </template>
    </div>

    <!-- Scroll to Top -->
    <button
      v-if="showScrollTop"
      @click="scrollToTop"
      :style="{
        position: 'fixed',
        bottom: isMobile ? '20px' : '30px',
        right: isMobile ? '20px' : '30px',
        width: isMobile ? '50px' : '60px',
        height: isMobile ? '50px' : '60px',
        background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
        border: 'none',
        borderRadius: '50%',
        color: '#1b1407',
        fontSize: isMobile ? '20px' : '24px',
        cursor: 'pointer',
        boxShadow: '0 10px 30px -12px rgba(194, 160, 104, 0.75)',
        transition: 'all 0.3s',
        zIndex: 1000,
        animation: 'fadeIn 0.3s',
      }"
      @mouseover="handleScrollButtonHoverIn"
      @mouseout="handleScrollButtonHoverOut"
    >
      <span :style="{ display: 'block', animation: 'bounce 1s infinite' }"
        ><Icon name="heroicons:arrow-up" :style="{ width: isMobile ? '20px' : '24px', height: isMobile ? '20px' : '24px' }" aria-hidden="true" /></span
      >
    </button>

    <!-- Contact Form Modal -->
    <div
      v-if="showContactForm"
      :style="{
        position: 'fixed',
        inset: 0,
        background: 'rgba(0, 0, 0, 0.8)',
        backdropFilter: 'blur(10px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 9999,
        padding: '20px',
      }"
      @click.self="showContactForm = false"
    >
      <div
        :style="{
          width: '100%',
          maxWidth: '500px',
          background: 'linear-gradient(135deg, rgba(15, 22, 33, 0.95), rgba(11, 16, 23, 0.98))',
          borderRadius: '24px',
          border: '1px solid rgba(255, 255, 255, 0.1)',
          padding: isMobile ? '24px' : '32px',
          position: 'relative',
        }"
      >
        <!-- Close Button -->
        <button
          @click="showContactForm = false"
          :style="{
            position: 'absolute',
            top: '16px',
            right: '16px',
            width: '36px',
            height: '36px',
            background: 'rgba(255, 255, 255, 0.1)',
            border: 'none',
            borderRadius: '50%',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: modalText.primary,
            fontSize: '18px',
          }"
        ><Icon name="heroicons:x-mark" class="w-[18px] h-[18px]" aria-hidden="true" /></button>

        <!-- Header -->
        <div :style="{ marginBottom: '24px', textAlign: 'center' }">
          <div :style="{ fontSize: '40px', marginBottom: '12px' }"><Icon name="heroicons:envelope" :style="{ width: '40px', height: '40px' }" aria-hidden="true" /></div>
          <h3 :style="{ color: modalText.primary, fontSize: '24px', fontWeight: 700, marginBottom: '8px' }">Send a Message</h3>
          <p :style="{ color: modalText.muted, fontSize: '14px' }">I'll get back to you as soon as possible</p>
        </div>

        <!-- Success Message -->
        <div
          v-if="contactFormSuccess"
          :style="{
            padding: '20px',
            background: 'rgba(169, 133, 77, 0.2)',
            border: '1px solid rgba(169, 133, 77, 0.4)',
            borderRadius: '16px',
            textAlign: 'center',
            marginBottom: '16px',
          }"
        >
          <div :style="{ fontSize: '40px', marginBottom: '8px' }"><Icon name="heroicons:check-circle" :style="{ width: '40px', height: '40px' }" aria-hidden="true" /></div>
          <p :style="{ color: '#a9854d', fontWeight: 600 }">Message sent successfully!</p>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="submitContactForm" :style="{ display: 'grid', gap: '16px' }">
          <div>
            <label :style="{ display: 'block', color: modalText.muted, fontSize: '14px', marginBottom: '6px' }">Your Name</label>
            <input
              v-model="contactForm.name"
              type="text"
              required
              :style="{
                width: '100%',
                padding: '14px 16px',
                background: 'rgba(255, 255, 255, 0.08)',
                border: '1px solid rgba(255, 255, 255, 0.15)',
                borderRadius: '12px',
                color: modalText.primary,
                fontSize: '15px',
                outline: 'none',
              }"
              placeholder="John Doe"
            />
          </div>
          <div>
            <label :style="{ display: 'block', color: modalText.muted, fontSize: '14px', marginBottom: '6px' }">Email Address</label>
            <input
              v-model="contactForm.email"
              type="email"
              required
              :style="{
                width: '100%',
                padding: '14px 16px',
                background: 'rgba(255, 255, 255, 0.08)',
                border: '1px solid rgba(255, 255, 255, 0.15)',
                borderRadius: '12px',
                color: modalText.primary,
                fontSize: '15px',
                outline: 'none',
              }"
              placeholder="john@example.com"
            />
          </div>
          <div>
            <label :style="{ display: 'block', color: modalText.muted, fontSize: '14px', marginBottom: '6px' }">Phone (Optional)</label>
            <input
              v-model="contactForm.phone"
              type="tel"
              :style="{
                width: '100%',
                padding: '14px 16px',
                background: 'rgba(255, 255, 255, 0.08)',
                border: '1px solid rgba(255, 255, 255, 0.15)',
                borderRadius: '12px',
                color: modalText.primary,
                fontSize: '15px',
                outline: 'none',
              }"
              placeholder="+60 12 345 6789"
            />
          </div>
          <div>
            <label :style="{ display: 'block', color: modalText.muted, fontSize: '14px', marginBottom: '6px' }">Message</label>
            <textarea
              v-model="contactForm.message"
              required
              rows="4"
              :style="{
                width: '100%',
                padding: '14px 16px',
                background: 'rgba(255, 255, 255, 0.08)',
                border: '1px solid rgba(255, 255, 255, 0.15)',
                borderRadius: '12px',
                color: modalText.primary,
                fontSize: '15px',
                outline: 'none',
                resize: 'vertical',
              }"
              placeholder="Your message..."
            ></textarea>
          </div>
          <button
            type="submit"
            :disabled="contactFormSubmitting"
            :style="{
              width: '100%',
              padding: '16px',
              background: contactFormSubmitting ? 'rgba(255, 255, 255, 0.1)' : 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
              border: 'none',
              borderRadius: '12px',
              color: contactFormSubmitting ? modalText.primary : '#1b1407',
              fontSize: '16px',
              fontWeight: 700,
              cursor: contactFormSubmitting ? 'not-allowed' : 'pointer',
              transition: 'all 0.3s',
            }"
          >
            {{ contactFormSubmitting ? 'Sending...' : 'Send Message' }}
          </button>
        </form>
      </div>
    </div>

    <!-- QR Code Modal -->
    <div
      v-if="showQRCode"
      :style="{
        position: 'fixed',
        inset: 0,
        background: 'rgba(0, 0, 0, 0.8)',
        backdropFilter: 'blur(10px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 9999,
        padding: '20px',
      }"
      @click.self="showQRCode = false"
    >
      <div
        :style="{
          width: '100%',
          maxWidth: '400px',
          background: 'linear-gradient(135deg, rgba(15, 22, 33, 0.95), rgba(11, 16, 23, 0.98))',
          borderRadius: '24px',
          border: '1px solid rgba(255, 255, 255, 0.1)',
          padding: isMobile ? '24px' : '32px',
          textAlign: 'center',
          position: 'relative',
        }"
      >
        <!-- Close Button -->
        <button
          @click="showQRCode = false"
          :style="{
            position: 'absolute',
            top: '16px',
            right: '16px',
            width: '36px',
            height: '36px',
            background: 'rgba(255, 255, 255, 0.1)',
            border: 'none',
            borderRadius: '50%',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: modalText.primary,
            fontSize: '18px',
          }"
        ><Icon name="heroicons:x-mark" class="w-[18px] h-[18px]" aria-hidden="true" /></button>

        <!-- Header -->
        <div :style="{ marginBottom: '24px' }">
          <div :style="{ fontSize: '40px', marginBottom: '12px' }"><Icon name="heroicons:qr-code" :style="{ width: '40px', height: '40px' }" aria-hidden="true" /></div>
          <h3 :style="{ color: modalText.primary, fontSize: '24px', fontWeight: 700, marginBottom: '8px' }">Scan to Connect</h3>
          <p :style="{ color: modalText.muted, fontSize: '14px' }">Share this profile instantly</p>
        </div>

        <!-- QR Code -->
        <div
          :style="{
            background: 'white',
            borderRadius: '20px',
            padding: '24px',
            display: 'inline-block',
            marginBottom: '20px',
          }"
        >
          <img
            :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(currentUrl)}`"
            alt="QR Code"
            :style="{ width: '200px', height: '200px', display: 'block' }"
          />
        </div>

        <!-- Profile Info -->
        <div :style="{ marginBottom: '16px' }">
          <p :style="{ color: modalText.primary, fontSize: '18px', fontWeight: 600, overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ profile.name }}</p>
          <p :style="{ color: modalText.muted, fontSize: '14px', overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ profile.position }}</p>
        </div>

        <!-- Copy Link Button -->
        <button
          @click="copyProfileLink"
          :style="{
            width: '100%',
            padding: '14px',
            background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
            border: 'none',
            borderRadius: '12px',
            color: '#1b1407',
            fontSize: '15px',
            fontWeight: 700,
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '8px',
          }"
        >
          <Icon name="heroicons:clipboard" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> Copy Profile Link
        </button>
      </div>
    </div>

    <!-- Social Share Modal -->
    <div
      v-if="showSocialShare"
      :style="{
        position: 'fixed',
        inset: 0,
        background: 'rgba(0, 0, 0, 0.8)',
        backdropFilter: 'blur(10px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 9999,
        padding: '20px',
      }"
      @click.self="showSocialShare = false"
    >
      <div
        :style="{
          width: '100%',
          maxWidth: '400px',
          background: 'linear-gradient(135deg, rgba(15, 22, 33, 0.95), rgba(11, 16, 23, 0.98))',
          borderRadius: '24px',
          border: '1px solid rgba(255, 255, 255, 0.1)',
          padding: isMobile ? '24px' : '32px',
          textAlign: 'center',
          position: 'relative',
        }"
      >
        <!-- Close Button -->
        <button
          @click="showSocialShare = false"
          :style="{
            position: 'absolute',
            top: '16px',
            right: '16px',
            width: '36px',
            height: '36px',
            background: 'rgba(255, 255, 255, 0.1)',
            border: 'none',
            borderRadius: '50%',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: modalText.primary,
            fontSize: '18px',
          }"
        ><Icon name="heroicons:x-mark" class="w-[18px] h-[18px]" aria-hidden="true" /></button>

        <!-- Header -->
        <div :style="{ marginBottom: '24px' }">
          <div :style="{ fontSize: '40px', marginBottom: '12px' }"><Icon name="heroicons:share" :style="{ width: '40px', height: '40px' }" aria-hidden="true" /></div>
          <h3 :style="{ color: modalText.primary, fontSize: '24px', fontWeight: 700, marginBottom: '8px' }">Share Profile</h3>
          <p :style="{ color: modalText.muted, fontSize: '14px' }">Share on your favorite platform</p>
        </div>

        <!-- Social Buttons Grid -->
        <div :style="{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '16px', marginBottom: '20px' }">
          <!-- Facebook -->
          <a
            :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:globe-alt" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">Facebook</span>
          </a>
          <!-- Twitter/X -->
          <a
            :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(profile.name + ' - ' + profile.position)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:at-symbol" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">X</span>
          </a>
          <!-- LinkedIn -->
          <a
            :href="`https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(profile.name)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:briefcase" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">LinkedIn</span>
          </a>
          <!-- WhatsApp -->
          <a
            :href="`https://wa.me/?text=${encodeURIComponent(profile.name + ' - ' + currentUrl)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:chat-bubble-left-right" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">WhatsApp</span>
          </a>
          <!-- Telegram -->
          <a
            :href="`https://t.me/share/url?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(profile.name)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:paper-airplane" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">Telegram</span>
          </a>
          <!-- Email -->
          <a
            :href="`mailto:?subject=${encodeURIComponent('Check out ' + profile.name)}&body=${encodeURIComponent('Here is ' + profile.name + '\'s profile: ' + currentUrl)}`"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:envelope" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">Email</span>
          </a>
          <!-- SMS -->
          <a
            :href="`sms:?body=${encodeURIComponent(profile.name + ' - ' + currentUrl)}`"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:device-phone-mobile" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">SMS</span>
          </a>
          <!-- Copy Link -->
          <button
            @click="copyProfileLink"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', background: 'none', border: 'none', cursor: 'pointer' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#c2a068', color: '#1b1407', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:clipboard" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">Copy</span>
          </button>
          <!-- Native device share (falls back to copy when unsupported) -->
          <button
            @click="shareProfile"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', background: 'none', border: 'none', cursor: 'pointer' }"
          >
            <div :style="{ width: '56px', height: '56px', background: 'rgba(194, 160, 104, 0.18)', border: '1px solid rgba(194, 160, 104, 0.4)', color: '#c2a068', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }"><Icon name="heroicons:share" :style="{ width: '24px', height: '24px' }" aria-hidden="true" /></div>
            <span :style="{ color: modalText.muted, fontSize: '12px' }">More</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Service Detail Modal -->
    <div
      v-if="showServiceModal && selectedService"
      :style="{
        position: 'fixed',
        inset: 0,
        background: 'rgba(0, 0, 0, 0.85)',
        backdropFilter: 'blur(12px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 9999,
        padding: '20px',
      }"
      @click.self="closeServiceModal"
    >
      <div
        :style="{
          width: '100%',
          maxWidth: '600px',
          maxHeight: '85vh',
          overflowY: 'auto',
          background: 'linear-gradient(135deg, rgba(15, 22, 33, 0.98), rgba(11, 16, 23, 0.99))',
          borderRadius: '28px',
          border: '1px solid rgba(255, 255, 255, 0.15)',
          padding: isMobile ? '24px' : '32px',
          position: 'relative',
        }"
      >
        <!-- Close Button -->
        <button
          @click="closeServiceModal"
          :style="{
            position: 'absolute',
            top: '16px',
            right: '16px',
            width: '40px',
            height: '40px',
            background: 'rgba(255, 255, 255, 0.1)',
            border: 'none',
            borderRadius: '50%',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: modalText.primary,
            fontSize: '20px',
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.2)'"
          @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
        ><Icon name="heroicons:x-mark" class="w-5 h-5" aria-hidden="true" /></button>

        <!-- Service Icon & Title -->
        <div :style="{ textAlign: 'center', marginBottom: '24px' }">
          <div :style="{ 
            width: '80px', 
            height: '80px', 
            margin: '0 auto 16px', 
            background: 'linear-gradient(135deg, rgba(194, 160, 104, 0.3), rgba(169, 133, 77, 0.3))', 
            borderRadius: '24px', 
            display: 'flex', 
            alignItems: 'center', 
            justifyContent: 'center',
            fontSize: '40px',
          }">
            <Icon :name="resolveServiceIcon(selectedService.icon)" :style="{ width: '40px', height: '40px' }" aria-hidden="true" />
          </div>
          <h3 :style="{ color: modalText.primary, fontSize: '28px', fontWeight: 700, marginBottom: '8px', overflowWrap: 'break-word', wordBreak: 'break-word' }">
            {{ selectedService.name }}
          </h3>
          <div v-if="selectedService.category" :style="{ display: 'flex', justifyContent: 'center', gap: '12px', flexWrap: 'wrap' }">
            <span :style="{ 
              padding: '6px 16px', 
              background: 'rgba(194, 160, 104, 0.2)', 
              border: '1px solid rgba(194, 160, 104, 0.4)',
              borderRadius: '20px', 
              color: '#e8d9b8', 
              fontSize: '13px',
              fontWeight: 500,
            }">
              {{ selectedService.category }}
            </span>
            <span v-if="selectedService.duration" :style="{ 
              padding: '6px 16px', 
              background: 'rgba(232, 217, 184, 0.15)', 
              border: '1px solid rgba(232, 217, 184, 0.4)',
              borderRadius: '20px', 
              color: '#e8d9b8', 
              fontSize: '13px',
              fontWeight: 500,
            }">
              <Icon name="heroicons:clock" class="w-3.5 h-3.5 inline-block align-[-0.2em] mr-1" aria-hidden="true" />{{ selectedService.duration }}
            </span>
          </div>
        </div>

        <!-- Price Section -->
        <div v-if="selectedService.price" :style="{ 
          textAlign: 'center', 
          marginBottom: '24px',
          padding: '20px',
          background: 'rgba(194, 160, 104, 0.1)',
          borderRadius: '16px',
          border: '1px solid rgba(194, 160, 104, 0.2)',
        }">
          <div :style="{ display: 'flex', alignItems: 'baseline', justifyContent: 'center', gap: '12px' }">
            <span :style="{ fontSize: '32px', fontWeight: 800, color: '#c2a068' }">
              {{ selectedService.price }}
            </span>
            <span v-if="selectedService.oldPrice" :style="{ 
              fontSize: '18px', 
              color: 'rgba(255, 255, 255, 0.4)', 
              textDecoration: 'line-through',
            }">
              {{ selectedService.oldPrice }}
            </span>
          </div>
        </div>

        <!-- Description -->
        <div v-if="selectedService.description" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            <Icon name="heroicons:document-text" class="w-4 h-4" aria-hidden="true" />
            Description
          </h4>
          <p :style="{ 
            color: modalText.muted, 
            fontSize: '15px', 
            lineHeight: '1.7',
            padding: '16px',
            background: 'rgba(255, 255, 255, 0.05)',
            borderRadius: '12px',
            overflowWrap: 'break-word',
            wordBreak: 'break-word',
          }" v-html="selectedService.description"></p>
        </div>

        <!-- Features -->
        <div v-if="selectedService.features && selectedService.features.length > 0" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            <Icon name="heroicons:sparkles" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
            Key Features
          </h4>
          <div :style="{ display: 'grid', gap: '10px' }">
            <div 
              v-for="(feature, idx) in selectedService.features" 
              :key="idx"
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '12px',
                padding: '12px 16px',
                background: 'rgba(255, 255, 255, 0.05)',
                borderRadius: '10px',
              }"
            >
              <span :style="{ color: '#c2a068', fontSize: '16px' }"><Icon name="heroicons:check" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /></span>
              <span :style="{ color: modalText.primary, fontSize: '14px', minWidth: 0, overflowWrap: 'break-word', wordBreak: 'break-word' }">
                {{ typeof feature === 'object' ? (feature.feature || feature.name || feature) : feature }}
              </span>
            </div>
          </div>
        </div>

        <!-- Tags -->
        <div v-if="selectedService.tags && selectedService.tags.length > 0" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            <Icon name="heroicons:tag" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
            Keywords
          </h4>
          <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '8px' }">
            <span 
              v-for="(tag, idx) in selectedService.tags" 
              :key="idx"
              :style="{
                padding: '6px 14px',
                background: 'rgba(194, 160, 104, 0.2)',
                border: '1px solid rgba(194, 160, 104, 0.3)',
                borderRadius: '16px',
                color: '#e8d9b8',
                fontSize: '13px',
                maxWidth: '100%',
                overflowWrap: 'break-word',
                wordBreak: 'break-word',
              }"
            >
              #{{ typeof tag === 'object' ? (tag.tag || tag.name || tag) : tag }}
            </span>
          </div>
        </div>

        <!-- Gallery -->
        <div v-if="selectedService.gallery && selectedService.gallery.length > 0" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            <Icon name="heroicons:photo" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
            Gallery
          </h4>
          <div :style="{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '10px' }">
            <div 
              v-for="(img, idx) in selectedService.gallery.slice(0, 6)" 
              :key="idx"
              :style="{
                paddingBottom: '100%',
                position: 'relative',
                borderRadius: '12px',
                overflow: 'hidden',
              }"
            >
              <img 
                :src="img.url || img" 
                :style="{
                  position: 'absolute',
                  inset: 0,
                  width: '100%',
                  height: '100%',
                  objectFit: 'cover',
                }"
              />
            </div>
          </div>
        </div>

        <!-- Video -->
        <div v-if="selectedService.video" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            <Icon name="heroicons:film" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
            Promo Video
          </h4>
          <a 
            :href="selectedService.video" 
            target="_blank"
            :style="{
              display: 'flex',
              alignItems: 'center',
              gap: '12px',
              padding: '16px',
              background: 'rgba(138, 106, 51, 0.1)',
              border: '1px solid rgba(138, 106, 51, 0.2)',
              borderRadius: '12px',
              textDecoration: 'none',
              transition: 'all 0.3s',
            }"
          >
            <Icon name="heroicons:play-circle" :style="{ width: '24px', height: '24px' }" aria-hidden="true" />
            <span :style="{ color: modalText.primary, fontSize: '14px', fontWeight: 500 }">Watch Video</span>
          </a>
        </div>

        <!-- Actions: Brochure & Booking -->
        <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap' }">
          <!-- Brochure Download -->
          <a
            v-if="selectedService.brochure"
            :href="selectedService.brochure"
            download
            :style="{
              flex: 1,
              minWidth: '150px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '10px',
              padding: '16px 24px',
              background: 'transparent',
              border: `1px solid ${hexToRgba(designSettings.accentColor, 0.32)}`,
              borderRadius: '14px',
              color: modalText.primary,
              fontSize: '15px',
              fontWeight: 600,
              textDecoration: 'none',
              transition: 'all 0.3s',
            }"
            @mouseover="(e) => e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.1)"
            @mouseout="(e) => e.currentTarget.style.background = 'transparent'"
          >
            <Icon name="heroicons:document-text" class="w-4 h-4 flex-shrink-0" :style="{ color: designSettings.accentColor }" aria-hidden="true" /> Download Brochure
          </a>

          <!-- Booking Button -->
          <a
            v-if="selectedService.bookingEnabled && selectedService.bookingUrl"
            :href="selectedService.bookingUrl"
            target="_blank"
            :style="{
              flex: 1,
              minWidth: '150px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '10px',
              padding: '16px 24px',
              background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
              borderRadius: '14px',
              color: '#1b1407',
              fontSize: '15px',
              fontWeight: 700,
              textDecoration: 'none',
              transition: 'all 0.3s',
              boxShadow: '0 12px 28px -14px rgba(194, 160, 104, 0.7)',
            }"
            @mouseover="(e) => e.currentTarget.style.transform = 'translateY(-2px)'"
            @mouseout="(e) => e.currentTarget.style.transform = 'translateY(0)'"
          >
            <Icon name="heroicons:calendar" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> Book Now
          </a>
        </div>
      </div>
    </div>

    <!-- Portfolio Detail Modal -->
    <div
      v-if="showPortfolioModal && selectedProject"
      :style="{
        position: 'fixed',
        inset: 0,
        background: 'rgba(0, 0, 0, 0.9)',
        backdropFilter: 'blur(12px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 9999,
        padding: '20px',
      }"
      @click.self="closePortfolioModal"
    >
      <div
        :style="{
          width: '100%',
          maxWidth: '800px',
          maxHeight: '90vh',
          overflowY: 'auto',
          background: 'linear-gradient(135deg, rgba(15, 22, 33, 0.98), rgba(11, 16, 23, 0.99))',
          borderRadius: '28px',
          border: '1px solid rgba(255, 255, 255, 0.15)',
          position: 'relative',
        }"
      >
        <!-- Close Button -->
        <button
          @click="closePortfolioModal"
          :style="{
            position: 'absolute',
            top: '16px',
            right: '16px',
            width: '40px',
            height: '40px',
            background: 'rgba(255, 255, 255, 0.1)',
            border: 'none',
            borderRadius: '50%',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: modalText.primary,
            fontSize: '20px',
            zIndex: 10,
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.2)'"
          @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
        ><Icon name="heroicons:x-mark" class="w-5 h-5" aria-hidden="true" /></button>

        <!-- Cover Image -->
        <div 
          v-if="selectedProject.cover_image"
          :style="{ 
            width: '100%', 
            height: '300px', 
            position: 'relative',
            overflow: 'hidden',
            borderRadius: '28px 28px 0 0',
          }"
        >
          <img 
            :src="selectedProject.cover_image" 
            :style="{ 
              width: '100%', 
              height: '100%', 
              objectFit: 'cover',
            }"
          />
          <div :style="{ position: 'absolute', inset: 0, background: 'linear-gradient(to top, rgba(11, 16, 23, 1) 0%, transparent 60%)' }" />
        </div>

        <!-- Content -->
        <div :style="{ padding: isMobile ? '24px' : '32px', marginTop: selectedProject.cover_image ? '-60px' : 0, position: 'relative', zIndex: 2 }">
          <!-- Title -->
          <h3 :style="{ color: modalText.primary, fontSize: '28px', fontWeight: 700, marginBottom: '16px', overflowWrap: 'break-word', wordBreak: 'break-word' }">
            {{ selectedProject.title }}
          </h3>

          <!-- Meta Info -->
          <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap', marginBottom: '20px' }">
            <span v-if="selectedProject.category" :style="{ padding: '6px 16px', background: 'rgba(194, 160, 104, 0.2)', border: '1px solid rgba(194, 160, 104, 0.4)', borderRadius: '20px', color: '#e8d9b8', fontSize: '13px' }">
              {{ selectedProject.category }}
            </span>
            <span v-if="selectedProject.client_name" :style="{ padding: '6px 16px', background: 'rgba(255, 255, 255, 0.08)', borderRadius: '20px', color: modalText.muted, fontSize: '13px', maxWidth: '100%', overflowWrap: 'break-word', wordBreak: 'break-word' }">
              <Icon name="heroicons:user" class="w-3.5 h-3.5 inline-block align-[-0.2em] mr-1" aria-hidden="true" />{{ selectedProject.client_name }}
            </span>
            <span v-if="selectedProject.date_completed" :style="{ padding: '6px 16px', background: 'rgba(255, 255, 255, 0.08)', borderRadius: '20px', color: modalText.muted, fontSize: '13px', maxWidth: '100%', overflowWrap: 'break-word', wordBreak: 'break-word' }">
              <Icon name="heroicons:calendar" class="w-3.5 h-3.5 inline-block align-[-0.2em] mr-1" aria-hidden="true" />{{ selectedProject.date_completed }}
            </span>
            <span v-if="selectedProject.location" :style="{ padding: '6px 16px', background: 'rgba(255, 255, 255, 0.08)', borderRadius: '20px', color: modalText.muted, fontSize: '13px', maxWidth: '100%', overflowWrap: 'break-word', wordBreak: 'break-word' }">
              <Icon name="heroicons:map-pin" class="w-3.5 h-3.5 inline-block align-[-0.2em] mr-1" aria-hidden="true" />{{ selectedProject.location }}
            </span>
          </div>

          <!-- Description -->
          <div v-if="selectedProject.description" :style="{ marginBottom: '24px' }">
            <p :style="{ color: modalText.muted, fontSize: '15px', lineHeight: '1.8', overflowWrap: 'break-word', wordBreak: 'break-word' }" v-html="selectedProject.description"></p>
          </div>

          <!-- Gallery -->
          <div v-if="selectedProject.gallery && selectedProject.gallery.length > 0" :style="{ marginBottom: '24px' }">
            <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
              <Icon name="heroicons:photo" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
              Project Gallery
            </h4>
            <div :style="{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '12px' }">
              <div 
                v-for="(img, idx) in selectedProject.gallery" 
                :key="idx"
                :style="{
                  paddingBottom: '100%',
                  position: 'relative',
                  borderRadius: '12px',
                  overflow: 'hidden',
                  cursor: 'pointer',
                  transition: 'transform 0.3s',
                }"
                @mouseover="(e) => e.currentTarget.style.transform = 'scale(1.05)'"
                @mouseout="(e) => e.currentTarget.style.transform = 'scale(1)'"
              >
                <img 
                  :src="typeof img === 'object' ? (img.url || img.image) : img" 
                  :style="{
                    position: 'absolute',
                    inset: 0,
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover',
                  }"
                />
              </div>
            </div>
          </div>

          <!-- Skills/Tags -->
          <div v-if="(selectedProject.skills_used && selectedProject.skills_used.length > 0) || (selectedProject.tags && selectedProject.tags.length > 0)" :style="{ marginBottom: '24px' }">
            <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
              <Icon name="heroicons:tag" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
              Skills & Tags
            </h4>
            <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '8px' }">
              <span 
                v-for="(skill, idx) in (selectedProject.skills_used || [])" 
                :key="'skill-' + idx"
                :style="{ padding: '6px 14px', background: 'rgba(169, 133, 77, 0.2)', border: '1px solid rgba(169, 133, 77, 0.3)', borderRadius: '16px', color: '#e8d9b8', fontSize: '13px' }"
              >{{ typeof skill === 'object' ? (skill.skill || skill.name) : skill }}</span>
              <span 
                v-for="(tag, idx) in (selectedProject.tags || [])" 
                :key="'tag-' + idx"
                :style="{ padding: '6px 14px', background: 'rgba(194, 160, 104, 0.2)', border: '1px solid rgba(194, 160, 104, 0.3)', borderRadius: '16px', color: '#e8d9b8', fontSize: '13px' }"
              >#{{ typeof tag === 'object' ? (tag.tag || tag.name) : tag }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap' }">
            <a
              v-if="selectedProject.url"
              :href="selectedProject.url"
              target="_blank"
              :style="{
                flex: 1,
                minWidth: '150px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '10px',
                padding: '16px 24px',
                background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
                borderRadius: '14px',
                color: '#1b1407',
                fontSize: '15px',
                fontWeight: 700,
                textDecoration: 'none',
                transition: 'all 0.3s',
                boxShadow: '0 12px 28px -14px rgba(194, 160, 104, 0.7)',
              }"
            >
              <Icon name="heroicons:link" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> View Project
            </a>
            <a
              v-if="selectedProject.pdf_download"
              :href="selectedProject.pdf_download"
              download
              :style="{
                flex: 1,
                minWidth: '150px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '10px',
                padding: '16px 24px',
                background: 'transparent',
                border: `1px solid ${hexToRgba(designSettings.accentColor, 0.32)}`,
                borderRadius: '14px',
                color: modalText.primary,
                fontSize: '15px',
                fontWeight: 600,
                textDecoration: 'none',
              }"
            >
              <Icon name="heroicons:document-text" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> Download PDF
            </a>
          </div>
        </div>
      </div>
    </div>
    <!-- Blog Detail Modal -->
    <div
      v-if="showBlogModal && selectedBlog"
      :style="{
        position: 'fixed',
        inset: 0,
        background: 'rgba(0, 0, 0, 0.9)',
        backdropFilter: 'blur(12px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 9999,
        padding: '20px',
      }"
      @click.self="closeBlogModal"
    >
      <div
        :style="{
          width: '100%',
          maxWidth: '800px',
          maxHeight: '90vh',
          overflowY: 'auto',
          background: 'linear-gradient(135deg, rgba(15, 22, 33, 0.98), rgba(11, 16, 23, 0.99))',
          borderRadius: '28px',
          border: '1px solid rgba(255, 255, 255, 0.15)',
          position: 'relative',
        }"
      >
        <!-- Close Button -->
        <button
          @click="closeBlogModal"
          :style="{
            position: 'absolute',
            top: '16px',
            right: '16px',
            width: '40px',
            height: '40px',
            background: 'rgba(255, 255, 255, 0.1)',
            border: 'none',
            borderRadius: '50%',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: modalText.primary,
            fontSize: '20px',
            zIndex: 10,
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.2)'"
          @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
        ><Icon name="heroicons:x-mark" class="w-5 h-5" aria-hidden="true" /></button>

        <!-- Media Gallery Carousel/Slider -->
        <!-- Priority: gallery array, then cover_image -->
        <div
            v-if="(selectedBlog.gallery && selectedBlog.gallery.length > 0) || selectedBlog.cover_image"
            :style="{
                width: '100%',
                position: 'relative',
                borderRadius: '28px 28px 0 0',
                overflow: 'hidden',
                background: '#000',
            }"
        >
            <!-- Check if we have multiple images for a slider/grid, or just one -->
            <div
                v-if="selectedBlog.gallery && selectedBlog.gallery.length > 0"
                :style="{
                    display: 'flex',
                    overflowX: 'auto',
                    scrollSnapType: 'x mandatory',
                    height: isMobile ? '250px' : '400px',
                    scrollbarWidth: 'none', // Hide scrollbar
                }"
            >
                <img
                    v-for="(img, idx) in selectedBlog.gallery"
                    :key="idx"
                    :src="getGalleryImageUrl(img)"
                    :style="{
                        minWidth: '100%',
                        height: '100%',
                        objectFit: 'contain', // contain to see full image
                        scrollSnapAlign: 'center',
                        background: 'black' // Letterboxing
                    }"
                />
            </div>
            <!-- Fallback to single cover image if no gallery array -->
            <div v-else :style="{ height: isMobile ? '200px' : '300px' }">
                <img
                    :src="selectedBlog.cover_image"
                    :style="{
                        width: '100%',
                        height: '100%',
                        objectFit: 'cover',
                    }"
                />
            </div>
             <div :style="{ position: 'absolute', bottom: 0, left: 0, right: 0, height: '60px', background: 'linear-gradient(to top, rgba(11, 16, 23, 1) 0%, transparent 100%)', pointerEvents: 'none' }" />
        </div>

        <!-- Content -->
        <div :style="{ padding: isMobile ? '24px' : '32px', position: 'relative', zIndex: 2 }">
          <!-- Meta Info Header -->
           <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap', marginBottom: '16px', alignItems: 'center' }">
             <span v-if="selectedBlog.category" :style="{ padding: '6px 14px', background: 'rgba(232, 217, 184, 0.15)', border: '1px solid rgba(232, 217, 184, 0.3)', borderRadius: '20px', color: '#e8d9b8', fontSize: '13px', fontWeight: 600 }">
              {{ selectedBlog.category }}
             </span>
             <span v-if="selectedBlog.published_date" :style="{ color: modalText.muted, fontSize: '14px', display: 'flex', alignItems: 'center', gap: '6px' }">
               <Icon name="heroicons:calendar" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> {{ selectedBlog.published_date }}
             </span>
             <span v-if="selectedBlog.reading_time" :style="{ color: modalText.muted, fontSize: '14px', display: 'flex', alignItems: 'center', gap: '6px' }">
               <Icon name="heroicons:clock" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> {{ selectedBlog.reading_time }}
             </span>
           </div>

          <!-- Title -->
          <h3 :style="{ color: modalText.primary, fontSize: isMobile ? '24px' : '32px', fontWeight: 700, marginBottom: '20px', lineHeight: 1.3, overflowWrap: 'break-word', wordBreak: 'break-word' }">
            {{ selectedBlog.title }}
          </h3>

          <!-- Author -->
          <div v-if="selectedBlog.author_name" :style="{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '24px', paddingBottom: '24px', borderBottom: '1px solid rgba(255, 255, 255, 0.1)' }">
              <div :style="{ width: '40px', height: '40px', background: hexToRgba(designSettings.accentColor, 0.16), border: `1px solid ${hexToRgba(designSettings.accentColor, 0.45)}`, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }">
                  <Icon name="heroicons:pencil" :style="{ width: '20px', height: '20px', color: designSettings.accentColor }" aria-hidden="true" />
              </div>
              <div :style="{ minWidth: 0 }">
                  <div :style="{ color: modalText.primary, fontWeight: 600, fontSize: '15px', overflowWrap: 'break-word', wordBreak: 'break-word' }">{{ selectedBlog.author_name }}</div>
                  <div :style="{ color: modalText.muted, fontSize: '13px' }">Author</div>
              </div>
          </div>

          <!-- Full Content (Rich Text) -->
          <div
            v-if="selectedBlog.content || selectedBlog.description"
            :style="{
                color: modalText.primary,
                fontSize: responsive.bodySize,
                lineHeight: '1.8',
                marginBottom: '32px',
                overflowWrap: 'break-word',
                wordBreak: 'break-word',
                whiteSpace: 'pre-wrap' // Preserve newlines if not HTML, but v-html handles HTML
            }"
            class="rich-text-content"
            v-html="selectedBlog.content || selectedBlog.description"
          />

          <!-- Tags -->
          <div v-if="selectedBlog.tags && selectedBlog.tags.length > 0" :style="{ marginBottom: '32px' }">
            <h4 :style="{ color: modalText.primary, fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
              <Icon name="heroicons:tag" class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
              Tags
            </h4>
            <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '8px' }">
              <span
                v-for="(tag, idx) in selectedBlog.tags"
                :key="'tag-' + idx"
                :style="{ padding: '6px 14px', background: 'rgba(255, 255, 255, 0.05)', border: '1px solid rgba(255, 255, 255, 0.1)', borderRadius: '16px', color: modalText.muted, fontSize: '13px' }"
              >#{{ typeof tag === 'object' ? (tag.tag || tag.name) : tag }}</span>
            </div>
          </div>

          <!-- Footer Actions -->
          <div v-if="selectedBlog.external_link" :style="{ borderTop: '1px solid rgba(255, 255, 255, 0.1)', paddingTop: '24px', display: 'flex', justifyContent: 'center' }">
             <a
                :href="selectedBlog.external_link"
                target="_blank"
                :style="{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '10px',
                  padding: '14px 32px',
                  background: 'linear-gradient(135deg, #f0e3c4 0%, #c2a068 46%, #a9854d 100%)',
                  borderRadius: '30px',
                  color: '#1b1407',
                  fontSize: '16px',
                  fontWeight: 700,
                  textDecoration: 'none',
                  boxShadow: '0 12px 30px -14px rgba(194, 160, 104, 0.7)',
                  transition: 'all 0.3s'
                }"
                @mouseover="(e) => e.target.style.transform = 'translateY(-2px)'"
                @mouseout="(e) => e.target.style.transform = 'translateY(0)'"
              >
                <Icon name="heroicons:link" class="w-4 h-4 flex-shrink-0" aria-hidden="true" /> Visit External Page
              </a>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { videoEmbedUrl } from "~/utils/videoEmbed";

// Composables
const { $api } = useNuxtApp();
const route = useRoute();

// State
const imageHover = ref(false);
const activeService = ref(null);
const activeTeam = ref(null);
const showScrollTop = ref(false);
const showHomeButton = ref(false);
const showEditButton = ref(false);
const currentNfcCardId = ref(null);
const isCardOwner = ref(false);
const companyCardRef = ref(null);
const windowWidth = ref(
  typeof window !== "undefined" ? window.innerWidth : 1200
);
const loading = ref(true);
const profileNotFound = ref(false);
const noProfileData = ref(false);  // New: when card exists but has no landing page data
const isPreviewMode = ref(false);
let scrollTimeout = null;
let resizeDebounce = null;

// Color helpers (shared between computed + template)
const hexToRgba = (hex, alpha = 1) => {
  let h = (hex || '').replace('#', '');
  if (h.length === 3) h = h.split('').map(c => c + c).join('');
  if (h.length !== 6) h = 'FFFFFF';
  const r = parseInt(h.substring(0, 2), 16);
  const g = parseInt(h.substring(2, 4), 16);
  const b = parseInt(h.substring(4, 6), 16);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

// Responsive breakpoints - granular tiers for all device sizes
const isSmallMobile = computed(() => windowWidth.value < 375);      // iPhone SE 1/2/3, very small phones
const isMobile = computed(() => windowWidth.value < 640);            // All phones
const isPhablet = computed(
  () => windowWidth.value >= 375 && windowWidth.value < 640
);                                                                   // Large phones (iPhone Pro Max, Pixel)
const isTablet = computed(
  () => windowWidth.value >= 640 && windowWidth.value < 1024
);                                                                   // iPads, tablets
const isSmallLaptop = computed(
  () => windowWidth.value >= 1024 && windowWidth.value < 1440
);                                                                   // MacBook 13", small laptops
const isDesktop = computed(() => windowWidth.value >= 1024);         // 1024px+
const isLargeDesktop = computed(() => windowWidth.value >= 1440);    // 27" monitors, 4K

// iOS safe area helpers (notch / dynamic island)
const safeTop = computed(() => {
  if (typeof window === "undefined") return "0px";
  const val = getComputedStyle(document.documentElement).getPropertyValue("--sat") || "0px";
  return val && val.trim() !== "" ? val : "0px";
});
const safeBottom = computed(() => {
  if (typeof window === "undefined") return "0px";
  const val = getComputedStyle(document.documentElement).getPropertyValue("--sab") || "0px";
  return val && val.trim() !== "" ? val : "0px";
});

// Responsive values - Modern design system, tiered for every device
const responsive = computed(() => {
  const sm = isSmallMobile.value;
  const ph = isPhablet.value;
  const mob = isMobile.value;
  const tab = isTablet.value;
  const sl = isSmallLaptop.value;
  const ld = isLargeDesktop.value;

  const cardStyleId = designSettings.cardStyle || 'minimal';

  const hexToRgba = (hex, alpha = 1) => {
    let h = (hex || '').replace('#', '');
    if (h.length === 3) h = h.split('').map(c => c + c).join('');
    if (h.length !== 6) h = 'FFFFFF';
    const r = parseInt(h.substring(0, 2), 16);
    const g = parseInt(h.substring(2, 4), 16);
    const b = parseInt(h.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  };
  const textColorRaw = designSettings.textColor || '#FFFFFF';
  const eff = (alpha) => hexToRgba(textColorRaw.startsWith('#') ? textColorRaw : '#FFFFFF', alpha);
  const effColorShadow = `0 0 36px ${hexToRgba(designSettings.cardEffectColor || designSettings.accentColor || '#c2a068', 0.35)}`;

  // Card style presets (mirrors CARD_STYLE_MAP + drives responsive fields)
  const cardPresets = {
    minimal: {
      cardBackground: `linear-gradient(160deg, ${hexToRgba(designSettings.backgroundColor ?? '#FFFFFF', 0.88)} 0%, ${eff(0.16)} 48%, rgba(255,255,255,0.95) 100%)`,
      cardBorder: '1px solid rgba(0, 0, 0, 0.06)',
      cardShadow: '0 6px 24px rgba(15, 23, 42, 0.06), 0 1px 0 rgba(255,255,255,0.8)',
      cardHoverShadow: '0 12px 40px rgba(15, 23, 42, 0.1)',
      borderRadius: sm ? '18px' : mob ? '22px' : tab ? '26px' : '28px',
      cardRadius: sm ? '14px' : mob ? '18px' : tab ? '24px' : '26px',
      backdropBlur: '6px',
    },
    glass: {
      cardBackground: 'rgba(255, 255, 255, 0.06)',
      cardBorder: '1px solid rgba(255, 255, 255, 0.16)',
      cardShadow: '0 12px 48px rgba(0, 0, 0, 0.28), 0 1px 0 rgba(255, 255, 255, 0.06)',
      cardHoverShadow: '0 18px 60px rgba(0, 0, 0, 0.35)',
      borderRadius: sm ? '22px' : mob ? '26px' : tab ? '30px' : '34px',
      cardRadius: sm ? '18px' : mob ? '22px' : tab ? '28px' : '32px',
      backdropBlur: '22px',
    },
    neumorph: {
      cardBackground: 'linear-gradient(145deg, #efece4, #ffffff)',
      cardBorder: '1px solid rgba(255,255,255,0.5)',
      cardShadow: '14px 14px 30px rgba(60, 70, 85,0.24), -14px -14px 30px rgba(255,255,255,0.95)',
      cardHoverShadow: '18px 18px 36px rgba(60, 70, 85,0.32), -18px -18px 36px rgba(255,255,255,1)',
      borderRadius: sm ? '22px' : mob ? '26px' : tab ? '30px' : '34px',
      cardRadius: sm ? '18px' : mob ? '22px' : tab ? '28px' : '32px',
      backdropBlur: '0px',
    },
    'frosted-bordered': {
      cardBackground: 'rgba(255, 255, 255, 0.04)',
      cardBorder: '3px solid rgba(255, 255, 255, 0.32)',
      cardShadow: '0 16px 48px rgba(0, 0, 0, 0.25), inset 0 0 0 1px rgba(255,255,255,0.05)',
      cardHoverShadow: '0 20px 60px rgba(0, 0, 0, 0.3)',
      borderRadius: sm ? '24px' : mob ? '28px' : tab ? '32px' : '36px',
      cardRadius: sm ? '20px' : mob ? '24px' : tab ? '30px' : '34px',
      backdropBlur: '24px',
    },
    'neon-outline': {
      cardBackground: 'rgba(6, 9, 13, 0.35)',
      cardBorder: '1px solid rgba(194, 160, 104, 0.55)',
      cardShadow: '0 0 0 1px rgba(194, 160, 104, 0.12), 0 10px 40px rgba(194, 160, 104, 0.22), 0 0 60px rgba(194, 160, 104,0.1)',
      cardHoverShadow: '0 0 0 1px rgba(194, 160, 104, 0.2), 0 16px 50px rgba(194, 160, 104, 0.32), 0 0 80px rgba(194, 160, 104,0.18)',
      borderRadius: sm ? '22px' : mob ? '26px' : tab ? '30px' : '34px',
      cardRadius: sm ? '18px' : mob ? '22px' : tab ? '28px' : '32px',
      backdropBlur: '14px',
    },
    'mica-blur': {
      cardBackground: `linear-gradient(180deg, ${hexToRgba('#FFFFFF', 0.2)} 0%, ${hexToRgba('#FFFFFF', 0.08)} 50%, ${hexToRgba('#FFFFFF', 0.04)} 100%)`,
      cardBorder: `1px solid ${eff(0.28)}`,
      cardShadow: `0 22px 55px rgba(15, 23, 42, 0.38), inset 0 1px 0 ${eff(0.42)}`,
      cardHoverShadow: `0 32px 70px rgba(15, 23, 42, 0.44), ${effColorShadow}`,
      borderRadius: sm ? '22px' : mob ? '26px' : tab ? '30px' : '32px',
      cardRadius: sm ? '18px' : mob ? '22px' : tab ? '28px' : '30px',
      backdropBlur: '28px',
      filterSaturate: 'saturate(160%)',
    },
    'liquid-chrome': {
      cardBackground: `linear-gradient(135deg, ${hexToRgba('#FFFFFF', 0.58)} 0%, ${hexToRgba('#efece4', 0.3)} 35%, ${hexToRgba('#FFFFFF', 0.48)} 55%, ${hexToRgba('#a8a190', 0.26)} 80%, ${hexToRgba('#FFFFFF', 0.52)} 100%)`,
      cardBorder: `1px solid ${eff(0.52)}`,
      cardShadow: `0 22px 46px rgba(15, 23, 42, 0.32), inset 0 0 0 1px ${eff(0.42)}, inset 0 1px 0 rgba(255,255,255,0.55)`,
      cardHoverShadow: `0 32px 60px rgba(15, 23, 42, 0.38), ${effColorShadow}`,
      borderRadius: sm ? '22px' : mob ? '26px' : tab ? '28px' : '30px',
      cardRadius: sm ? '18px' : mob ? '22px' : tab ? '26px' : '28px',
      backdropBlur: '22px',
    },
    'aurora-tint': {
      cardBackground: `linear-gradient(140deg, ${hexToRgba('#a9854d', 0.35)} 0%, ${eff(0.3)} 42%, ${hexToRgba('#8a6a33', 0.34)} 100%)`,
      cardBorder: `1px solid ${eff(0.42)}`,
      cardShadow: `0 26px 60px rgba(169, 133, 77, 0.26), inset 0 1px 0 rgba(255,255,255,0.3)`,
      cardHoverShadow: `0 36px 72px rgba(138, 106, 51, 0.3), ${effColorShadow}`,
      borderRadius: sm ? '24px' : mob ? '28px' : tab ? '32px' : '34px',
      cardRadius: sm ? '20px' : mob ? '24px' : tab ? '30px' : '32px',
      backdropBlur: '20px',
    },
    'cyber-lines': {
      cardBackground: `linear-gradient(135deg, ${hexToRgba('#0b1017', 0.9)} 0%, ${hexToRgba('#0b1017', 0.6)} 100%)`,
      cardBorder: `1.5px solid transparent; background-origin: padding-box, border-box; background-clip: padding-box, border-box; background-image: linear-gradient(135deg, ${hexToRgba('#0b1017', 0.9)}, ${hexToRgba('#0b1017', 0.55)}), linear-gradient(135deg, ${designSettings.cardEffectColor ?? '#c2a068'} 0%, ${hexToRgba('#a9854d', 0.85)} 45%, ${hexToRgba('#8a6a33', 0.85)} 100%);`,
      cardShadow: `0 22px 46px rgba(194, 160, 104, 0.22), 0 0 0 1px ${eff(0.12)}`,
      cardHoverShadow: `0 32px 64px rgba(194, 160, 104, 0.32), 0 0 28px ${eff(0.45)}`,
      borderRadius: sm ? '20px' : mob ? '24px' : tab ? '28px' : '30px',
      cardRadius: sm ? '16px' : mob ? '20px' : tab ? '26px' : '28px',
      backdropBlur: '16px',
    },
    'dot-matrix-frame': {
      cardBackground: `${hexToRgba(designSettings.backgroundColor ?? '#FFFFFF', 0.9)}`,
      cardBorder: `2px dotted ${eff(0.9)}`,
      cardShadow: `0 16px 40px ${eff(0.18)}, inset 0 0 0 3px ${hexToRgba(designSettings.backgroundColor ?? '#FFFFFF', 0.92)}`,
      cardHoverShadow: `0 26px 56px ${eff(0.28)}`,
      borderRadius: sm ? '20px' : mob ? '24px' : tab ? '28px' : '30px',
      cardRadius: sm ? '16px' : mob ? '20px' : tab ? '26px' : '28px',
      backdropBlur: '10px',
    },
    'velvet-matte': {
      cardBackground: `linear-gradient(160deg, ${hexToRgba('#0b1017', 0.9)} 0%, ${hexToRgba('#0f1621', 0.82)} 100%)`,
      cardBorder: `1px solid ${eff(0.28)}`,
      cardShadow: `0 26px 56px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255,255,255,0.06)`,
      cardHoverShadow: `0 36px 72px rgba(0, 0, 0, 0.48), ${effColorShadow}`,
      borderRadius: sm ? '22px' : mob ? '26px' : tab ? '30px' : '32px',
      cardRadius: sm ? '18px' : mob ? '22px' : tab ? '28px' : '30px',
      backdropBlur: '20px',
    },
  };
  const cs = cardPresets[cardStyleId] || cardPresets.minimal;

  // === FORCE SOLID-COLOR CONTAINERS (gradient reserved ONLY for root page background) ===
  // Ink & Brass: dark literals are flat ink surfaces with a warm brass hairline;
  // light literals fall back to Paper.
  {
    const hx = (textColorRaw || '#FFFFFF').replace('#', '');
    const hex6 = hx.length === 3 ? hx.split('').map(c => c + c).join('') : hx.padEnd(6, '0');
    const r = parseInt(hex6.substring(0, 2), 16);
    const g = parseInt(hex6.substring(2, 4), 16);
    const b = parseInt(hex6.substring(4, 6), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    const isDarkTheme = luminance > 0.55;
    // Ink gradient surface — flat enough to read as one panel, deep enough to feel premium
    cs.cardBackground = isDarkTheme
      ? 'linear-gradient(180deg, rgba(17, 22, 31, 0.86) 0%, rgba(9, 13, 19, 0.95) 100%)'
      : '#f7f5f0';
    cs.backdropBlur = '0px';
    // Warm brass hairline instead of a neutral grey border
    cs.cardBorder = isDarkTheme
      ? '1px solid rgba(194, 160, 104, 0.14)'
      : '1px solid rgba(138, 106, 51, 0.20)';
    // Tighter, architectural radius — reads as an art-directed panel rather
    // than a soft, generic "app card".
    cs.cardRadius = sm ? '12px' : ph ? '14px' : mob ? '15px' : tab ? '16px' : '18px';
    // Deep, diffuse elevation (not a flat drop shadow) — reads as engineered light
    // plus a low-alpha brass rim so the panel edge catches the ambient glow.
    cs.cardShadow = isDarkTheme
      ? '0 22px 48px -26px rgba(0, 0, 0, 0.92), 0 0 46px -14px rgba(194, 160, 104, 0.26), inset 0 1px 0 rgba(194, 160, 104, 0.18)'
      : '0 16px 36px -22px rgba(28, 26, 21, 0.45), 0 0 40px -16px rgba(138, 106, 51, 0.22), inset 0 1px 0 rgba(138, 106, 51, 0.18)';
    cs.cardHoverShadow = isDarkTheme
      ? '0 30px 64px -28px rgba(0, 0, 0, 0.98), 0 0 0 1px rgba(194, 160, 104, 0.18)'
      : '0 24px 50px -24px rgba(28, 26, 21, 0.5)';
    if (cs.filterSaturate) delete cs.filterSaturate;
  }

  return {
    // Container — deliberate content width with generous, safe-area-aware
    // horizontal gutters so content never touches the viewport edge.
    // (Tiers sm / ph / tab / sl / ld are mutually exclusive; the previous
    //  `mob` tier was shadowed by `ph` and never applied.)
    containerPadding: sm
      ? "60px 16px 28px"
      : ph
        ? "64px 20px 32px"
        : tab
          ? "72px 32px 40px"
          : sl
            ? "76px 40px 48px"
            : "80px 48px 56px",
    // sm / ph / mob are all < 640px, so the earlier tiers all collapse to the
    // same full-width value — only the tablet and desktop tiers differ.
    pageMaxWidth: mob ? '100%' : tab ? '768px' : '1200px',

    // Preview/editorial display face — the public profile pairs an editorial
    // serif (Fraunces) for the masthead + section titles with Inter for body.
    displayFont: '"Fraunces", "Iowan Old Style", Georgia, "Times New Roman", serif',

    // Profile image - scales gracefully (legacy circle, still used by modals)
    profileSize: sm ? "112px" : ph ? "128px" : mob ? "148px" : tab ? "180px" : "208px",
    profileBorder: cardStyleId === 'minimal' || cardStyleId === 'neumorph'
      ? "4px solid #ffffff"
      : "4px solid rgba(255, 255, 255, 0.15)",
    // Hero portrait plate — the portrait is a first-class art-directed element,
    // never a small avatar, so it gets its own (larger) scale.
    portraitSize: sm ? "176px" : ph ? "204px" : mob ? "240px" : tab ? "288px" : ld ? "404px" : "356px",

    // Typography - fluid modular scale
    nameSize: sm ? "42px" : ph ? "48px" : mob ? "56px" : tab ? "66px" : ld ? "92px" : "78px",
    h2Size: sm ? "21px" : ph ? "22px" : mob ? "24px" : tab ? "26px" : "30px",
    // Editorial eyebrow (small caps label set above titles / over sections)
    eyebrowSize: sm ? "10px" : "11px",
    h3Size: sm ? "17px" : ph ? "18px" : mob ? "20px" : tab ? "23px" : "27px",
    h4Size: sm ? "14px" : ph ? "15px" : mob ? "16px" : tab ? "17px" : "19px",
    bodySize: sm ? "14px" : mob ? "15px" : "16px",
    smallSize: sm ? "12px" : mob ? "13px" : "14px",

    // Text colors - respect Text Color picker (Colors section)
    textPrimary: hexToRgba(designSettings.textColor ?? '#ffffff', 0.92),
    textMuted: hexToRgba(designSettings.textColor ?? '#ffffff', 0.7),
    textSubtle: hexToRgba(designSettings.textColor ?? '#ffffff', 0.52),

    // Card styling - driven by cardStyle preset
    cardPadding: sm ? "14px" : ph ? "16px" : mob ? "18px" : tab ? "30px" : "38px",
    cardMarginBottom: sm ? "18px" : mob ? "22px" : tab ? "28px" : "32px",
    cardBackground: cs.cardBackground,
    cardBorder: cs.cardBorder,
    cardShadow: cs.cardShadow,
    cardHoverShadow: cs.cardHoverShadow,
    cardBackdropBlur: cs.backdropBlur,

    // Quick actions
    actionPadding: sm ? "12px 10px" : ph ? "14px 12px" : mob ? "16px 14px" : "20px",
    actionIconSize: sm ? "22px" : ph ? "26px" : mob ? "28px" : "36px",
    actionGap: sm ? "8px" : ph ? "10px" : mob ? "12px" : "16px",

    // Grid columns - tighter minmax on tiny screens
    statsGrid: sm
      ? "repeat(auto-fit, minmax(90px, 1fr))"
      : mob
        ? "repeat(auto-fit, minmax(110px, 1fr))"
        : "repeat(auto-fit, minmax(160px, 1fr))",
    servicesGrid: sm
      ? "repeat(auto-fit, minmax(130px, 1fr))"
      : mob
        ? "repeat(auto-fit, minmax(160px, 1fr))"
        : "repeat(auto-fit, minmax(220px, 1fr))",
    galleryGrid: sm ? "1fr" : mob ? "repeat(2, 1fr)" : "repeat(3, 1fr)",

    // Icon sizes
    serviceIconSize: sm ? "22px" : ph ? "26px" : mob ? "28px" : "40px",
    serviceIconBox: sm ? "46px" : ph ? "52px" : mob ? "58px" : "72px",
    contactIconBox: sm ? "48px" : ph ? "54px" : mob ? "58px" : "64px",
    contactIconSize: sm ? "22px" : ph ? "26px" : mob ? "28px" : "32px",

    // Borders and radius - Softer edges, never fully sharp on mobile
    borderRadius: cs.borderRadius,
    cardRadius: cs.cardRadius,
    smallRadius: sm ? "10px" : mob ? "12px" : "16px",

    // Badge styling
    badgePadding: sm ? "8px 14px" : mob ? "10px 18px" : "12px 24px",

    // Gaps - More breathing room
    flexGap: sm ? "8px" : mob ? "12px" : "20px",
    gridGap: sm ? "12px" : mob ? "16px" : "24px",
    sectionGap: sm ? "18px" : mob ? "24px" : tab ? "28px" : "32px",
    // Internal sub-section rhythm for the combined cards (Company + Team,
    // About + Education + Awards): the symmetric space above AND below each
    // in-card divider, so the divider never sits closer to one side.
    subSectionGap: sm ? "18px" : mob ? "22px" : tab ? "26px" : "30px",

    // Safe-area aware top offset for hero (avoids overlap with fixed buttons + iOS notch)
    heroTopOffset: sm ? "48px" : ph ? "52px" : mob ? "56px" : tab ? "60px" : "64px",

    // Masthead switches to the asymmetric two-column composition from tablet up;
    // phones keep a stacked (but art-directed) centre composition.
    heroTwoCol: !sm && !ph && !mob,
  };
});

// Profile Data - Will be loaded from API (matches ProfileBuilder fields)
const profile = ref({
  name: "",
  qualification: "",
  position: "",
  pronouns: "",
  tagline: "",
  bio: "",
  phone: "",
  email: "",
  website: "",
  address: "",
  education: [],
  certifications: [],
  profileStats: [], // User-defined statistics
});

const coverBanner = ref(null); // Cover banner image

const profileImage = ref(
  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23c2a068'/%3E%3Ctext x='75' y='95' font-size='60' fill='white' text-anchor='middle' font-family='Arial' font-weight='bold'%3E?%3C/text%3E%3C/svg%3E"
);

// ---- Hero masthead presentation helpers --------------------------------------
// A real uploaded photo is presented as the large portrait plate. When a profile
// has no image we render a designed monogram plate (initials on brass) instead of
// a generic avatar, so the hero never reads as an unfinished placeholder.
const heroPortrait = computed(() => {
  const src = String(profileImage.value || "").trim();
  return src.startsWith("data:image/svg+xml") ? "" : src;
});

const heroInitials = computed(() => {
  const name = String(profile.value?.name || "").trim();
  if (!name) return "";
  const parts = name.split(/\s+/).filter(Boolean);
  const letters =
    parts.length === 1
      ? parts[0].slice(0, 2)
      : (parts[0][0] || "") + (parts[parts.length - 1][0] || "");
  return letters.toUpperCase();
});

// Split the display name into an italic "given name" line + a bold "family name"
// line, so the masthead reads like an editorial cover, not one bold string.
const heroNameLines = computed(() => {
  const name = String(profile.value?.name || "").trim();
  if (!name) return { leading: "", trailing: "" };
  const parts = name.split(/\s+/).filter(Boolean);
  if (parts.length === 1) return { leading: "", trailing: parts[0] };
  return { leading: parts[0], trailing: parts.slice(1).join(" ") };
});

// Added: Design config and visible fields control
const visibleFieldsConfig = ref([]);
const buttonClasses = ref('');
const currentThemeConfig = ref(null);

// Ink & Brass page backdrop — mirrors the public landing "ink-surface" glow.
const INK_BRASS_BACKDROP =
  'radial-gradient(120% 90% at 12% 0%, rgba(194, 160, 104, 0.10) 0%, transparent 46%), ' +
  'radial-gradient(90% 80% at 92% 12%, rgba(194, 160, 104, 0.08) 0%, transparent 52%), ' +
  '#06090d';

// Design variables - for dynamic styling
const designSettings = reactive({
  backgroundColor: '#06090d',
  textColor: '#f7f5f0',
  accentColor: '#c2a068',
  bgGradient: INK_BRASS_BACKDROP,
  cardEffectColor: '#c2a068',
  cardEffectOpacity: 0.55,
  fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif',
  theme: 'default',
  buttonStyle: 'solid',
  cardStyle: 'minimal',
  bgAnimation: 'none',
});

// System palette lock — the public profile always renders in "Ink & Brass"
// regardless of any colour customisation saved in the Profile Builder, so every
// card matches the landing page. Called after the API/preview data is applied.
const applyInkBrassPalette = () => {
  designSettings.backgroundColor = '#06090d';
  designSettings.textColor = '#f7f5f0';
  designSettings.accentColor = '#c2a068';
  designSettings.cardEffectColor = '#c2a068';
  designSettings.cardEffectOpacity = 0.55;
  designSettings.bgGradient = INK_BRASS_BACKDROP;
};

// Light vs dark theme, from the page background. Parts of this page were
// drawn for the dark "Ink & Brass" palette only (cream text, pale-brass chips);
// now that the owner's theme is honoured they need to follow it.
const isLightTheme = computed(() => {
  let h = String(designSettings.backgroundColor || '').replace('#', '');
  if (h.length === 3) h = h.split('').map((c) => c + c).join('');
  if (!/^[0-9a-fA-F]{6}$/.test(h)) return false;
  const [r, g, b] = [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16) / 255);
  return 0.2126 * r + 0.7152 * g + 0.0722 * b > 0.6;
});

// Pale-brass chip text works on dark themes only; light themes use their text colour.
const chipTextColor = computed(() => (isLightTheme.value ? (designSettings.textColor || '#1f2937') : '#e8d9b8'));

// Modals always use a dark panel, whatever the theme, so their text is light.
const modalText = { primary: 'rgba(247, 245, 240, 0.92)', muted: 'rgba(247, 245, 240, 0.7)' };

// Colours the API resolved for this profile (NfcCardController::resolvedDesign):
// the owner's theme/colours, or the system palette when the plan does not
// include the chosen theme. Authoritative over the raw saved fields.
const applyResolvedDesign = (design) => {
  if (!design || typeof design !== 'object') return;
  if (design.locked) {
    applyInkBrassPalette();
    return;
  }
  if (design.background_color) designSettings.backgroundColor = design.background_color;
  if (design.text_color) designSettings.textColor = design.text_color;
  if (design.accent_color) designSettings.accentColor = design.accent_color;
  // null = solid background colour (a custom colour replaced the theme gradient)
  designSettings.bgGradient = design.bg_gradient || null;
  if (design.theme) designSettings.theme = design.theme;
};

// Loading skeleton bar colour - translucent so it never flashes a fixed colour
// on the user's configured background. Light text => dark theme => translucent
// white bars; dark text => light theme => translucent black bars.
const skeletonBarClass = computed(() => {
  const hex = (designSettings.textColor || "#ffffff").replace("#", "");
  let h = hex;
  if (h.length === 3) h = h.split("").map((c) => c + c).join("");
  if (h.length !== 6) return "bg-white/10";
  const r = parseInt(h.slice(0, 2), 16);
  const g = parseInt(h.slice(2, 4), 16);
  const b = parseInt(h.slice(4, 6), 16);
  const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;
  return luminance > 140 ? "bg-black/10" : "bg-white/10";
});

// Card style library (matches ProfileBuilder DESIGN_LIBRARY)
const CARD_STYLE_MAP = {
  minimal: 'bg-white/90 backdrop-blur-sm rounded-2xl border border-white/60',
  glass: 'bg-white/10 backdrop-blur-xl rounded-3xl border border-white/20 shadow-xl',
  neumorph: 'bg-gray-100 rounded-3xl shadow-[12px_12px_24px_rgba(0,0,0,0.06),-12px_-12px_24px_rgba(255,255,255,0.9)]',
  'frosted-bordered': 'bg-white/5 backdrop-blur-xl rounded-3xl border-[3px] border-white/40',
  'neon-outline': 'bg-black/20 backdrop-blur-md rounded-3xl border border-[#c2a068]/50 shadow-[0_0_30px_rgba(194,160,104,0.3)]',
};
const computedCardClass = computed(() => CARD_STYLE_MAP[designSettings.cardStyle] || CARD_STYLE_MAP.minimal);
provide('cardStyleClass', computedCardClass);

// ==================== A. SECTION LAYOUT CONFIGURATION ====================
// Section Layout from API - now uses granular section IDs matching Layout Designer
const sectionLayout = ref([]);
const fieldLayout = ref({}); // Field layout with sub-section enabled states

// Default section order if no layout configured
// Logical flow: Introduction → About → Company → Services → Work/Portfolio → Blog → Contact → Location → Social → Team → Gallery → Education → Awards → vCard
const defaultSections = [
  'hero',       // 1. Introduction - always first
  'about',      // 2. About Me - personal introduction
  'company',    // 3. Company - business context
  'services',   // 4. Services - what you offer
  'portfolio',  // 5. Portfolio - work samples
  'blog',       // 6. Blog - thought leadership
  'contact',    // 7. Contact - how to reach
  'location',   // 8. Location - where to find
  'social',     // 9. Social - connect online
  'team',       // 10. Team - who else is involved
  'gallery',    // 11. Gallery - visual showcase
  'education',  // 12. Education - credentials
  'awards',     // 13. Awards - achievements
  'vcard',      // 14. vCard - always last, save contact action
];

// Computed: Ordered sections based on Layout Designer
const orderedSections = computed(() => {
  if (sectionLayout.value?.length) {
    // Direct mapping - section IDs now match between Layout Designer and Landing Page
    const enabledSections = sectionLayout.value
      .filter(s => s.id === 'hero' || s.enabled !== false)
      .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
      .map(s => s.id);

    // Always include vCard at the end if not already present
    if (!enabledSections.includes('vcard')) {
      enabledSections.push('vcard');
    }
    
    return enabledSections;
  }
  return defaultSections;
});

// Check if a section should be visible (based on Layout Designer config)
// Now uses direct section ID matching (no more mapping needed)
const isSectionVisible = (sectionId) => {
  // Hero section always visible
  if (sectionId === 'hero') return true;
  
  // Default: show all sections if no layout config
  if (!sectionLayout.value?.length) return true;
  
  const section = sectionLayout.value.find(s => s.id === sectionId);
  return section ? section.enabled !== false : true;
};

// Check if a sub-section is visible within a combined section
// Uses fieldLayout._subSections to check if the sub-section is enabled
const isSubSectionVisible = (parentSectionId, subSectionId) => {
  // First check if parent section is visible
  if (!isSectionVisible(parentSectionId)) return false;
  
  // Check fieldLayout._subSections for sub-section enabled state
  const subSections = fieldLayout.value?._subSections;
  if (!subSections) return true; // Default: show if no config
  
  const key = `${parentSectionId}-${subSectionId}`;
  return subSections[key] !== false;
};

// Check if a specific field is visible
// Uses fieldLayout[sectionId] to check if the field is enabled
const isFieldVisible = (sectionId, fieldKey) => {
  const fields = fieldLayout.value?.[sectionId];
  if (!fields || !Array.isArray(fields)) return true; // Default: show if no config
  
  const field = fields.find(f => f.field_key === fieldKey);
  return field ? field.enabled !== false : true; // Default to visible if not found
};

// ==================== SECTION DATA AVAILABILITY ====================
// These computed properties check if a section has data to display
// Sections only show if: 1) they have data AND 2) they're enabled in layout

// Profile videos: player URLs from the API (`videos`, plan-gated server-side)
// or, in Live Preview, built locally from the builder's links.
const introVideoEmbed = ref(null);
const companyVideoEmbed = ref(null);

const hasCompanyData = computed(() => {
  return !!(company.value.name || company.value.department || company.value.logo || company.value.logoText || companyVideoEmbed.value);
});

const hasAboutData = computed(() => {
  return !!(profile.value.bio || stats.value.length > 0 || introVideoEmbed.value);
});

const hasServicesData = computed(() => {
  return services.value.length > 0;
});

const hasContactData = computed(() => {
  return contactMethods.value.some(c => c.subtitle);
});

const hasLocationData = computed(() => {
  const hasNewFormat = !!(address.value.line1 || address.value.line2 || address.value.line3);
  const hasLegacyFormat = !!(address.value.name || address.value.street || address.value.cityState);
  // Dedupe: if new 3-line format is identical in content to legacy combined, avoid double counting
  if (hasNewFormat && hasLegacyFormat) {
    const newFlat = [address.value.line1, address.value.line2, address.value.line3]
      .filter(Boolean).join(", ").toLowerCase().replace(/\s+/g, " ");
    const legacyFlat = [address.value.street, address.value.area, address.value.postalCode, address.value.cityState, address.value.country]
      .filter(Boolean).join(", ").toLowerCase().replace(/\s+/g, " ");
    // If either is empty string after join, treat as duplicate-companion
    if (!newFlat || !legacyFlat) return true;
    // Treat as single source if they look the same (avoid "both blocks both valid" scenario)
    const intersection = newFlat.length && legacyFlat.length &&
      (newFlat.includes(legacyFlat) || legacyFlat.includes(newFlat) ||
        legacyFlat.substring(0, 40) === newFlat.substring(0, 40));
    return true;
  }
  return hasNewFormat || hasLegacyFormat;
});

const hasSocialData = computed(() => {
  return socialLinks.value.length > 0;
});

const hasTeamData = computed(() => {
  return teamMembers.value.length > 0;
});

const hasGalleryData = computed(() => {
  return gallery.value.length > 0;
});

const hasPortfolioData = computed(() => {
  return portfolio.value.length > 0;
});

const hasBlogData = computed(() => {
  return blogPosts.value.length > 0;
});

// ==================== SECTION SHOW LOGIC ====================
// Combine data availability with layout visibility
// Default: show section if it has data (unless explicitly disabled in layout)
// Section IDs now match directly between Layout Designer and Landing Page

// Main section visibility
// Note: companyTeam and profileAchievements are the combined section IDs from Layout Designer
const showCompanySection = computed(() => hasCompanyData.value && isSubSectionVisible('companyTeam', 'company'));
const showAboutSection = computed(() => hasAboutData.value && isSubSectionVisible('profileAchievements', 'about'));
const showServicesSection = computed(() => hasServicesData.value && isSectionVisible('services'));
const showContactSection = computed(() => hasContactData.value && isSectionVisible('contact'));
const showLocationSection = computed(() => hasLocationData.value && isSectionVisible('location'));
const showSocialSection = computed(() => hasSocialData.value && isSectionVisible('social'));
const showGallerySection = computed(() => hasGalleryData.value && isSectionVisible('gallery'));
const showPortfolioSection = computed(() => hasPortfolioData.value && isSectionVisible('portfolio'));
const showBlogSection = computed(() => hasBlogData.value && isSectionVisible('blog'));

// Sub-section visibility (within combined cards)
const showTeamSection = computed(() => hasTeamData.value && isSubSectionVisible('companyTeam', 'team'));
const showEducationSection = computed(() => (profile.value.education?.length > 0 || profile.value.certifications?.length > 0) && isSubSectionVisible('profileAchievements', 'education'));
const showAwardsSection = computed(() => awards.value.length > 0 && isSubSectionVisible('profileAchievements', 'awards'));

const company = ref({
  logo: null,          // Company logo image
  logoText: "",
  name: "",
  registrationNo: "",
  department: "",
  description: "",     // Company description
  industry: "",        // Industry type
  establishedYear: "", // Year founded
  employeeCount: "",   // Number of employees
});

const address = ref({
  name: "",
  street: "",
  area: "",
  cityState: "",
  country: "",
  postalCode: "",  // Postal code
  mapUrl: "",
  // New simplified 3-line format
  line1: "",
  line2: "",
  line3: "",
});

// Build display lines: new 3-line format first, fallback to legacy separate fields
// Also deduplicate - if legacy content is the same as new content, only show one copy
const addressDisplayLines = computed(() => {
  const newFormat = [address.value.line1, address.value.line2, address.value.line3].filter(Boolean);

  // Legacy fallback
  const legacy = [];
  if (address.value.street) legacy.push(address.value.street);
  if (address.value.area) legacy.push(address.value.area);
  const line3Legacy = [
    address.value.postalCode,
    address.value.cityState,
  ].filter(Boolean).join(" ");
  if (line3Legacy) legacy.push(line3Legacy);
  if (address.value.country) legacy.push(address.value.country);

  if (newFormat.length === 0) return legacy;

  // If both exist: check if legacy lines are actually duplicates of newFormat
  // (happens when loadProfile auto-composes legacy from new format in PremiumProfileBuilder load)
  const newFlat = newFormat.join(", ").toLowerCase().replace(/\s+/g, " ");
  const legacyFlat = legacy.join(", ").toLowerCase().replace(/\s+/g, " ");

  if (legacy.length > 0) {
    // If first 60 chars match or one is substring of another → treat as same source
    const similar =
      (newFlat.length > 10 && legacyFlat.length > 10 &&
        (newFlat.substring(0, 60) === legacyFlat.substring(0, 60) ||
         newFlat.includes(legacyFlat) || legacyFlat.includes(newFlat)));
    if (similar) {
      return newFormat; // Only return new format (avoid duplicate display)
    }
  }

  return newFormat;
});

// Hero identity meta — a compact "company · location" line under the name.
// Only surfaces high-signal identity data; never duplicates the position badge.
const heroMeta = computed(() => {
  const out = [];

  // Company name (respects companyTeam > company field layout when configured)
  const companyName = (company.value.name || "").trim();
  if (companyName && isFieldVisible("companyTeam", "companyName")) {
    out.push({ icon: "heroicons:building-office-2", label: companyName });
  }

  // Location — prefer city/country, fall back to first address display line
  const locality = [
    (address.value.cityState || "").trim(),
    (address.value.country || "").trim(),
  ].filter(Boolean).join(", ");
  const locationLabel = locality || (addressDisplayLines.value?.[0] || "").trim();
  if (locationLabel) {
    out.push({ icon: "heroicons:map-pin", label: locationLabel });
  }

  return out;
});

// ==================== C. QUICK ACTIONS (dynamically generated) ====================
// Quick Actions - dynamically generated from contactMethods (respects field visibility)
const quickActions = computed(() => {
  const actions = [];
  
  // Phone (check field visibility)
  if (contactMethods.value[0]?.subtitle && isFieldVisible('contact', 'phone')) {
    actions.push({ icon: "heroicons:phone", label: "Call", action: "call" });
  }
  
  // Email
  if (contactMethods.value[1]?.subtitle) {
    actions.push({ icon: "heroicons:envelope", label: "Email", action: "email" });
  }
  
  // WhatsApp (check field visibility)
  if (contactMethods.value[2]?.subtitle && contactMethods.value[2]?.href && isFieldVisible('contact', 'whatsapp')) {
    actions.push({ icon: "heroicons:chat-bubble-left-right", label: "Chat", action: "whatsapp" });
  }
  
  // Appointment (check booking_integration feature)
  if (appointmentLink.value && isFeatureEnabled('booking_integration')) {
    actions.push({ icon: "heroicons:calendar", label: "Book", action: "appointment" });
  }
  
  // Payment
  if (paymentButton.value.url) {
    actions.push({ icon: "heroicons:credit-card", label: paymentButton.value.text || "Pay", action: "payment" });
  }
  
  // Website
  if (contactMethods.value[3]?.subtitle) {
    actions.push({ icon: "heroicons:globe-alt", label: "Website", action: "website" });
  }
  
  return actions;
});

// Default stats (fallback)
const stats = ref([]);

const services = ref([]);

// Service details
const serviceDetails = ref({
  features: [],      // Service features
  price: "",         // Current price
  oldPrice: "",      // Previous price
  duration: "",      // Service duration
  tags: [],          // Service tags
  brochure: null,    // PDF brochure
  bookingEnabled: false,
  bookingUrl: "",
});

const contactMethods = ref([
  {
    icon: "M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 0 0-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z",
    label: "Phone",
    subtitle: "",
    href: "tel:",
    color: "linear-gradient(135deg, #c2a068 0%, #a9854d 100%)",
  },
  {
    icon: "M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z",
    label: "Email",
    subtitle: "",
    href: "mailto:",
    color: "linear-gradient(135deg, #e8d9b8 0%, #8a6a33 100%)",
  },
  {
    icon: "M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z",
    label: "WhatsApp",
    subtitle: "Chat with me",
    href: "",
    color: "linear-gradient(135deg, #c2a068 0%, #c2a068 100%)",
  },
  {
    icon: "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z",
    label: "Website",
    subtitle: "",
    href: "",
    color: "linear-gradient(135deg, #a9854d 0%, #c2a068 100%)",
  },
  {
    icon: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z",
    label: "Address",
    subtitle: "",
    href: "",
    color: "linear-gradient(135deg, #c2a068 0%, #c2a068 100%)",
  },
]);

// Filtered contact methods based on field visibility AND data presence
const filteredContactMethods = computed(() => {
  return contactMethods.value.filter((method, idx) => {
    // Map index to field key: 0=phone, 1=email, 2=whatsapp, 3=website, 4=address
    const fieldKeyMap = { 0: 'phone', 1: 'email', 2: 'whatsapp', 3: 'website', 4: 'address' };
    const fieldKey = fieldKeyMap[idx];
    
    // First check: method must have data (non-empty subtitle)
    if (!method.subtitle || method.subtitle.trim() === '') {
      return false;
    }
    
    // Second check: method must have a href (except address which may just show text)
    if (!method.href && fieldKey !== 'address') {
      return false;
    }
    
    // Third check: visibility settings for phone and whatsapp
    if (fieldKey === 'phone' || fieldKey === 'whatsapp') {
      return isFieldVisible('contact', fieldKey);
    }
    
    return true;
  });
});

const socialLinks = ref([]);
// Social links will be populated dynamically from API data (social_links table)
// via loadProfileData() or loadPreviewData() functions

const teamMembers = ref([]);

// ==================== B. SUPPLEMENTARY FIELDS ====================
// Working Hours
const workingHours = ref([]);

// Expertise & Skills
const expertise = ref([]);

// Awards
const awards = ref([]);

// Gallery
const gallery = ref([]);

// Portfolio / Projects
const portfolio = ref([]);

// Blog Posts
const blogPosts = ref([]);

// Payment & Appointment
const paymentButton = ref({
  text: "",
  url: "",
});

const appointmentLink = ref("");

// Feature Settings (controlled by user in BusinessProfileBuilder)
// Feature keys match those in getFeatureIcon() in BusinessProfileBuilder.vue
const features = ref({
  contact_form: true,         // Contact Form - send messages
  vcard_download: true,       // Downloadable vCard
  qr_code: true,              // QR Code Sharing
  remove_branding: false,     // Remove Branding/Watermark (default: show watermark)
  booking_integration: true,  // Booking Integration
  social_sharing: true,       // Social Sharing buttons
});

// NFCGo branding visibility — resolved by the plan + admin policy on the API
// response (show_nfcgo_branding). Preview mode falls back to the owner toggle.
const showNfcgoBranding = ref(true);

// Available features (assigned by admin) - if empty, all features are available
const availableFeatures = ref([]);

// Feature order (from BusinessProfileBuilder)
const featureOrder = ref([]);

// Contact Form state
const showContactForm = ref(false);
const contactForm = ref({
  name: '',
  email: '',
  phone: '',
  message: '',
});
const contactFormSubmitting = ref(false);
const contactFormSuccess = ref(false);

// QR Code state
const showQRCode = ref(false);

// Social Share state
const showSocialShare = ref(false);

// Service Modal state
const showServiceModal = ref(false);
const selectedService = ref(null);

// Open service detail modal
const openServiceModal = (service) => {
  selectedService.value = service;
  showServiceModal.value = true;
};

// Handle service card click: direct link opens URL, otherwise opens details modal
const handleServiceClick = (service) => {
  activeService.value = null;
  if (service.linkType === 'Direct Link' && service.url) {
    window.open(service.url, '_blank', 'noopener,noreferrer');
    return;
  }
  openServiceModal(service);
};

// Close service detail modal
const closeServiceModal = () => {
  showServiceModal.value = false;
  selectedService.value = null;
};

// Portfolio Modal state
const showPortfolioModal = ref(false);
const selectedProject = ref(null);

// Open portfolio detail modal
const openPortfolioModal = (project) => {
  selectedProject.value = project;
  showPortfolioModal.value = true;
};

// Blog Modal State
const showBlogModal = ref(false);
const selectedBlog = ref(null);

// Open blog detail modal
const openBlogModal = (blog) => {
  console.log('Opening blog modal for:', blog);
  selectedBlog.value = blog;
  showBlogModal.value = true;
};

const closeBlogModal = () => {
  showBlogModal.value = false;
  selectedBlog.value = null;
};

// Helper function to extract URL from gallery image items
// Gallery items can be plain URL strings or objects with { type, url, name }
const getGalleryImageUrl = (item) => {
  if (!item) return null;
  if (typeof item === 'string') return item;
  return item.url || item.image || null;
};

// Close portfolio detail modal
const closePortfolioModal = () => {
  showPortfolioModal.value = false;
  selectedProject.value = null;
};

// Check if a feature is enabled based on the user's toggle setting.
// Backend does not persist available_features, so we rely solely on the user's
// feature toggles (features[featureKey] !== false means enabled).
const isFeatureEnabled = (featureKey) => features.value[featureKey] !== false;

// Links tab options (server: NfcCardController::linkSettings → effective_features).
const linkTarget = computed(() => (features.value.open_new_tab === false ? '_self' : '_blank'));
const trackLinkClick = (social) => {
  if (isPreviewMode.value || !social?.id || features.value.click_tracking === false) return;
  // Fire-and-forget: never delay or block the visitor's navigation.
  $api.post(`/links/${social.id}/click`).catch(() => {});
};

// Preview (builder → localStorage) mirrors the server's effective_features
// rules (NfcCardController::effectiveFeatures), since it never hits the API:
//  - the owner's Profile-tab toggle (vcardEnabled…) switches its action off;
//  - an admin-controlled action not offered to this plan stays hidden.
const PREVIEW_FEATURE_ALIASES = { vcard_download: 'vcardEnabled', contact_form: 'contactFormEnabled', qr_code: 'qrCodeEnabled' };
const ADMIN_CONTROLLED_ACTIONS = ['vcard_download', 'contact_form', 'qr_code', 'booking_integration'];
const applyPreviewFeatureRules = (data) => {
  Object.entries(PREVIEW_FEATURE_ALIASES).forEach(([key, alias]) => {
    if (data?.[alias] === false || data?.features?.[alias] === false) features.value[key] = false;
  });
  if (data?.openNewTab === false || data?.features?.openNewTab === false) features.value.open_new_tab = false;
  if (Array.isArray(data?.availableFeatures) && data.availableFeatures.length > 0) {
    ADMIN_CONTROLLED_ACTIONS.forEach((key) => {
      if (!data.availableFeatures.includes(key)) features.value[key] = false;
    });
  }
};

// Whether the Quick Actions section should be shown (any quick-action feature enabled)
const quickActionKeys = ['vcard_download', 'contact_form', 'qr_code', 'social_sharing', 'booking_integration'];
const hasQuickActions = computed(() => quickActionKeys.some(key => isFeatureEnabled(key)));
const quickActionCount = computed(() => quickActionKeys.filter(key => isFeatureEnabled(key)).length);

// Load preview data from localStorage (matches BusinessProfileBuilder.vue profileData structure)
const loadPreviewData = (data) => {
  console.log('Loading preview data:', data);
  
  // ============ PROFILE SECTION ============
  profile.value.name = data.name || "Your Name";
  profile.value.qualification = data.qualification || "";
  profile.value.position = data.position || data.title || "Your Position";
  profile.value.pronouns = data.pronouns || "";
  profile.value.tagline = data.tagline || "";
  profile.value.bio = data.bio || "";
  profile.value.email = data.email || "";
  profile.value.phone = data.contactNumber || data.phone || "";
  profile.value.website = data.website || "";
  profile.value.address = data.address || "";
  profile.value.education = data.education || [];
  profile.value.certifications = data.certifications || [];
  profile.value.profileStats = data.profileStats || [];

  // Profile Image - priority: profilePicture (new) > image (legacy) > initials
  if (data.profilePicture) {
    profileImage.value = data.profilePicture;
  } else if (data.image) {
    profileImage.value = data.image;
  } else if (data.name) {
    const initials = data.name.split(" ").map((n) => n[0]).join("").substring(0, 2).toUpperCase();
    profileImage.value = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23c2a068'/%3E%3Ctext x='75' y='95' font-size='60' fill='white' text-anchor='middle' font-family='Arial' font-weight='bold'%3E${initials}%3C/text%3E%3C/svg%3E`;
  }

  // Cover Banner
  if (data.coverBanner) {
    coverBanner.value = data.coverBanner;
  }

  // ============ COMPANY SECTION ============
  if (data.companyLogo) {
    company.value.logo = data.companyLogo;
  }
  company.value.logoText = data.companyLogoText || "CO";
  company.value.name = data.companyName || "";
  company.value.registrationNo = data.companyRegistrationNo || "";
  company.value.department = data.companyDepartment || "";
  company.value.description = data.companyDescription || "";
  company.value.industry = data.industry || "";
  company.value.establishedYear = data.establishedYear || "";
  company.value.employeeCount = data.employeeCount || "";

  // ============ ADDRESS SECTION ============
  // New simplified 3-line format takes priority
  address.value.line1 = data.address1 || "";
  address.value.line2 = data.address2 || "";
  address.value.line3 = data.address3 || "";

  // Legacy separate address fields (for fallback display)
  // Only populate legacy if 3-line format is empty AND vice-versa - avoid duplicate display
  const hasNewFormat = !!(address.value.line1 || address.value.line2 || address.value.line3);
  // Also auto-compose legacy fields from new format for backward compatibility (in case other code reads them)
  if (hasNewFormat) {
    const newAll = [address.value.line1, address.value.line2, address.value.line3].filter(Boolean);
    // Split the combined lines reasonably so legacy readers still get something without dupe display
    if (!data.addressStreet && !data.addressName && newAll.length > 0) {
      address.value.street = newAll[0];
      if (newAll[1]) address.value.area = newAll[1];
      if (newAll[2]) {
        // Try to detect postal + city pattern in last line
        const last = newAll[2];
        const m = last.match(/^(\d{4,6})\s+(.+)$/);
        if (m) {
          address.value.postalCode = m[1];
          address.value.cityState = m[2];
        } else {
          address.value.cityState = last;
        }
      }
    } else {
      address.value.name = data.addressName || "";
      address.value.street = data.addressStreet || (data.address ? data.address : "");
      address.value.area = data.addressArea || "";
      address.value.cityState = data.addressCityState || "";
      address.value.country = data.addressCountry || "";
      address.value.postalCode = data.postalCode || "";
    }
  } else {
    address.value.name = data.addressName || "";
    address.value.street = data.addressStreet || (data.address ? data.address : "");
    address.value.area = data.addressArea || "";
    address.value.cityState = data.addressCityState || "";
    address.value.country = data.addressCountry || "";
    address.value.postalCode = data.postalCode || "";
  }
  address.value.mapUrl = data.addressMapUrl || data.mapUrl || "";

  // ============ CONTACT METHODS ============
  contactMethods.value[0].subtitle = data.phoneNumber || data.contactNumber || data.phone || "";
  contactMethods.value[0].href = (data.phoneNumber || data.contactNumber) ? `tel:${(data.phoneNumber || data.contactNumber).replace(/\s/g, '')}` : "";
  contactMethods.value[1].subtitle = data.emailAddress || data.email || "";
  contactMethods.value[1].href = (data.emailAddress || data.email) ? `mailto:${data.emailAddress || data.email}` : "";
  contactMethods.value[2].subtitle = data.whatsappNumber || data.whatsapp ? "Chat with me" : "";
  contactMethods.value[2].href = (data.whatsappNumber || data.whatsapp) ? `https://wa.me/${(data.whatsappNumber || data.whatsapp).replace(/[^0-9]/g, '')}` : "";
  contactMethods.value[3].subtitle = data.websiteUrl || data.website || "";
  contactMethods.value[3].href = data.websiteUrl || data.website || "";
  // Reset index 4 by default first (to avoid stale values)
  contactMethods.value[4].subtitle = "";
  contactMethods.value[4].href = "";
  contactMethods.value[4].label = "";

  // Contact Address for "Get In Touch" (uses profile contact address, NOT company location)
  // DEDUP: Only show contact address if it is DIFFERENT from Our Location section
  const contactAddressRaw = data.address || '';
  if (contactAddressRaw) {
    const locationFlat = ([address.value.line1, address.value.line2, address.value.line3,
      address.value.street, address.value.area, address.value.postalCode, address.value.cityState, address.value.country])
      .filter(Boolean).join(", ").toLowerCase().replace(/\s+/g, " ");
    const contactFlat = contactAddressRaw.toLowerCase().replace(/\s+/g, " ").trim();
    const isDuplicate = contactFlat.length > 10 && locationFlat.length > 10 &&
      (contactFlat === locationFlat || locationFlat.includes(contactFlat) || contactFlat.includes(locationFlat) ||
        contactFlat.substring(0, 50) === locationFlat.substring(0, 50));
    if (!isDuplicate) {
      contactMethods.value[4].subtitle = contactAddressRaw;
      contactMethods.value[4].href = `https://maps.google.com/?q=${encodeURIComponent(contactAddressRaw)}`;
      contactMethods.value[4].label = "Contact Address";
    }
  }

  // ============ STATS ============
  if (data.stats && Array.isArray(data.stats)) {
    stats.value = data.stats.filter(stat => stat.num && stat.label);
  }

  // ============ EXPERTISE & SKILLS ============
  if (data.expertise && Array.isArray(data.expertise)) {
    expertise.value = data.expertise.filter(skill => skill.name);
  }

  // ============ SERVICES ============
  if (data.services && Array.isArray(data.services)) {
    services.value = data.services
      .filter(service => service.name || service.serviceName)
      .map(service => ({
        ...service,
        name: service.name || service.service_name || service.serviceName,
        icon: service.icon || "heroicons:rocket-launch",
        category: service.category || service.service_category || service.serviceCategory || "",
        linkType: service.link_type || service.serviceLinkType || service.service_link_type || "Description & Features",
        url: service.url || service.serviceUrl || service.service_url || "",
        duration: service.duration || service.service_duration || service.serviceDuration || "",
        description: service.description || service.service_description || service.serviceDescription || "",
        features: service.features || service.service_features || service.serviceFeatures || [],
        tags: service.tags || service.service_tags || service.serviceTags || [],
        price: service.price || service.service_price || service.servicePrice || "",
        oldPrice: service.old_price || service.oldPrice || service.service_old_price || service.serviceOldPrice || "",
        gallery: service.gallery || service.service_gallery || service.images || [],
        video: service.video || service.service_video || service.promo_video || "",
        brochure: service.brochure || service.service_brochure || "",
        bookingUrl: service.booking_url || service.bookingUrl || "",
        bookingEnabled: service.booking_enabled !== undefined ? service.booking_enabled : (service.bookingEnabled !== undefined ? service.bookingEnabled : false)
      }));
    // Load service details for first service
    if (services.value.length > 0) {
      serviceDetails.value = {
        features: data.services[0]?.features || [],
        price: data.services[0]?.price || "",
        oldPrice: data.services[0]?.oldPrice || "",
        duration: data.services[0]?.duration || "",
        tags: data.services[0]?.tags || [],
        brochure: data.brochure || data.services[0]?.brochure || "",
        bookingUrl: data.bookingUrl || data.services[0]?.bookingUrl || "",
      };
    }
  }

  // ============ GALLERY ============
  if (data.gallery && Array.isArray(data.gallery)) {
    gallery.value = data.gallery.filter(img => img.url || img.image);
  }

  // ============ TEAM MEMBERS ============
  if (data.teamMembers && Array.isArray(data.teamMembers)) {
    teamMembers.value = data.teamMembers.filter(member => member.name);
  }

  // ============ PORTFOLIO ============
  if (data.portfolio && Array.isArray(data.portfolio)) {
    portfolio.value = data.portfolio
      .filter(p => p.title || p.name || p.projectTitle)
      .map(project => ({
        ...project,
        title: project.title || project.projectTitle || project.name || "",
        description: project.description || project.projectDetails || "",
        cover_image: project.cover_image || project.coverImage || project.image || "",
        gallery: project.gallery || project.projectGallery || project.images || [],
        client_name: project.client_name || project.clientName || "",
        location: project.location || "",
        date_completed: project.date_completed || project.dateCompleted || project.completionDate || "",
        url: project.url || project.projectUrl || project.externalLink || "",
        pdf_download: project.pdf_download || project.pdfDownload || "",
        skills_used: project.skills_used || project.skillsUsed || [],
        tags: project.tags || [],
        category: project.category || project.portfolioCategory || "",
      }));
  } else if (data.projects && Array.isArray(data.projects)) {
    portfolio.value = data.projects
      .filter(p => p.title || p.name)
      .map(project => ({
        ...project,
        title: project.title || project.projectTitle || project.name || "",
        description: project.description || project.projectDetails || "",
        cover_image: project.cover_image || project.coverImage || project.image || "",
        gallery: project.gallery || project.projectGallery || project.images || [],
        client_name: project.client_name || project.clientName || "",
        location: project.location || "",
        date_completed: project.date_completed || project.dateCompleted || project.completionDate || "",
        url: project.url || project.projectUrl || project.externalLink || "",
        pdf_download: project.pdf_download || project.pdfDownload || "",
        skills_used: project.skills_used || project.skillsUsed || [],
        tags: project.tags || [],
        category: project.category || project.portfolioCategory || "",
      }));
  }

  // ============ BLOG ============
  if (data.blogPosts && Array.isArray(data.blogPosts)) {
    blogPosts.value = data.blogPosts.filter(p => p.title);
  }

  // ============ AWARDS ============
  if (data.awards && Array.isArray(data.awards)) {
    awards.value = data.awards.filter(a => a.title || a.name);
  }

  // ============ SOCIAL LINKS ============
  if (data.links && Array.isArray(data.links) && data.links.length > 0) {
    socialLinks.value = data.links
      .filter((link) => link.is_active && link.url)
      .sort((a, b) => (a.order || 0) - (b.order || 0))
      .map((link) => ({
        emoji: getPlatformIcon(link.platform),
        name: link.title || link.platform,
        href: link.url,
        color: getPlatformColor(link.platform),
      }));
  }

  // ============ QUICK ACTIONS ============
  if (data.appointmentUrl) {
    appointmentLink.value = data.appointmentUrl;
  }
  if (data.paymentUrl || data.paymentButtonText) {
    paymentButton.value = {
      url: data.paymentUrl || "",
      text: data.paymentButtonText || "Pay Now",
    };
  }

  // ============ DESIGN SETTINGS ============
  if (data.backgroundColor) {
    designSettings.backgroundColor = data.backgroundColor;
  }
  if (data.textColor) {
    designSettings.textColor = data.textColor;
  }
  if (data.accentColor || data.accent_color) {
    designSettings.accentColor = data.accentColor || data.accent_color;
  }
  // Builder preview: a solid background (no gradient) must replace the page
  // default instead of keeping it, so the preview matches the live profile.
  if (data.backgroundColor) {
    designSettings.bgGradient = data.bgGradient || data.bg_gradient || null;
  } else if (data.bgGradient || data.bg_gradient) {
    designSettings.bgGradient = data.bgGradient || data.bg_gradient;
  }
  if (data.cardEffectColor || data.card_effect_color) {
    designSettings.cardEffectColor = data.cardEffectColor || data.card_effect_color;
  }
  if (data.cardEffectOpacity !== undefined && data.cardEffectOpacity !== null) {
    designSettings.cardEffectOpacity = Number(data.cardEffectOpacity);
  } else if (data.card_effect_opacity !== undefined && data.card_effect_opacity !== null) {
    designSettings.cardEffectOpacity = Number(data.card_effect_opacity);
  }
  if (data.cardStyle || data.card_style) {
    designSettings.cardStyle = data.cardStyle || data.card_style;
  }
  if (data.bgAnimation || data.bg_animation) {
    designSettings.bgAnimation = data.bgAnimation || data.bg_animation;
  }
  if (data.font) {
    const fontMap = {
      'inter': 'Inter, sans-serif',
      'roboto': 'Roboto, sans-serif',
      'playfair': 'Playfair Display, serif',
      'poppins': 'Poppins, sans-serif',
      'montserrat': 'Montserrat, sans-serif',
      'lora': 'Lora, serif',
      'nunito': 'Nunito, sans-serif',
    };
    designSettings.fontFamily = fontMap[data.font] || data.font;
  }
  if (data.buttonStyle) {
    designSettings.buttonStyle = data.buttonStyle;
  }
  if (data.theme) {
    designSettings.theme = data.theme;
  }

  // ============ SECTION LAYOUT ============
  if (data.sectionLayout && Array.isArray(data.sectionLayout)) {
    sectionLayout.value = data.sectionLayout;
    console.log('Section layout loaded from preview:', sectionLayout.value);
  } else {
    console.log('No sectionLayout in preview data, using defaults');
  }
  
  // ============ FIELD LAYOUT (with sub-section states) ============
  if (data.fieldLayout && typeof data.fieldLayout === 'object') {
    fieldLayout.value = data.fieldLayout;
    console.log('Field layout loaded from preview:', fieldLayout.value);
  }

  // ============ FEATURE SETTINGS ============
  // Load available features (assigned by admin)
  if (data.availableFeatures && Array.isArray(data.availableFeatures)) {
    availableFeatures.value = data.availableFeatures;
    console.log('Available features (assigned by admin):', availableFeatures.value);
  } else {
    // If no availableFeatures provided, set to empty array (meaning admin didn't assign any features)
    availableFeatures.value = [];
    console.log('No features assigned by admin');
  }
  
  console.log('Raw features from preview data:', data.features);
  if (data.features && typeof data.features === 'object' && Object.keys(data.features).length > 0) {
    // Merge features - preview data overrides defaults
    Object.keys(data.features).forEach(key => {
      features.value[key] = data.features[key];
      console.log(`  → Setting feature "${key}" to:`, data.features[key]);
    });
    console.log('Features after merge:', JSON.stringify(features.value));
  } else {
    console.log('No features in preview data, using defaults:', features.value);
  }
  applyPreviewFeatureRules(data);
  introVideoEmbed.value = videoEmbedUrl(data.introVideo);
  companyVideoEmbed.value = videoEmbedUrl(data.companyVideo);

  // Preview has no server-side plan policy — mirror the owner's branding toggle.
  showNfcgoBranding.value = features.value.remove_branding !== true;

  if (data.featureOrder && Array.isArray(data.featureOrder)) {
    featureOrder.value = data.featureOrder;
    console.log('Feature order loaded from preview:', featureOrder.value);
  }

  profileNotFound.value = false;
  console.log('Preview data loaded successfully');
};

// Helper functions for platform data
const getPlatformIcon = (platform) => {
  const platformMap = {
    instagram: "heroicons:camera",
    facebook: "heroicons:globe-alt",
    tiktok: "heroicons:musical-note",
    youtube: "heroicons:play-circle",
    spotify: "heroicons:musical-note",
    whatsapp: "heroicons:chat-bubble-left-right",
    x: "heroicons:at-symbol",
    snapchat: "heroicons:face-smile",
    linkedin: "heroicons:briefcase",
    telegram: "heroicons:paper-airplane",
    website: "heroicons:globe-alt",
    email: "heroicons:envelope",
    // New platforms
    reddit: "heroicons:chat-bubble-bottom-center-text",
    pinterest: "heroicons:map-pin",
    wechat: "heroicons:chat-bubble-left-right",
    douyin: "heroicons:musical-note",
    discord: "heroicons:chat-bubble-oval-left",
    threads: "heroicons:at-symbol",
    xiaohongshu: "heroicons:book-open",
    quora: "heroicons:question-mark-circle",
    custom: "heroicons:link",
  };
  return platformMap[platform?.toLowerCase()] || "heroicons:link";
};

// Maps the ProfileBuilder SERVICE_ICONS emoji set to Heroicons names so the
// public page always renders SVG icons, even when the stored service icon is
// an emoji selected by the user in the builder.
const SERVICE_EMOJI_ICON_MAP = {
  "🚀": "heroicons:rocket-launch",
  "💼": "heroicons:briefcase",
  "🏢": "heroicons:building-office",
  "👨‍💻": "heroicons:computer-desktop",
  "👩‍💼": "heroicons:user-circle",
  "📊": "heroicons:chart-bar",
  "📈": "heroicons:arrow-trending-up",
  "📉": "heroicons:arrow-trending-down",
  "🎯": "heroicons:cursor-arrow-rays",
  "💡": "heroicons:light-bulb",
  "🛠️": "heroicons:wrench-screwdriver",
  "🔧": "heroicons:wrench",
  "⚙️": "heroicons:cog-6-tooth",
  "🔩": "heroicons:cog-8-tooth",
  "🏗️": "heroicons:building-office-2",
  "🏭": "heroicons:building-office-2",
  "📱": "heroicons:device-phone-mobile",
  "💻": "heroicons:computer-desktop",
  "🖥️": "heroicons:computer-desktop",
  "🖨️": "heroicons:printer",
  "🌐": "heroicons:globe-alt",
  "☁️": "heroicons:cloud",
  "🔒": "heroicons:lock-closed",
  "🛡️": "heroicons:shield-check",
  "📷": "heroicons:camera",
  "🎨": "heroicons:paint-brush",
  "✏️": "heroicons:pencil",
  "🖌️": "heroicons:paint-brush",
  "🎬": "heroicons:film",
  "🎵": "heroicons:musical-note",
  "📝": "heroicons:document-text",
  "📚": "heroicons:book-open",
  "🏥": "heroicons:building-office-2",
  "💊": "heroicons:beaker",
  "🦷": "heroicons:face-smile",
  "👁️": "heroicons:eye",
  "💉": "heroicons:beaker",
  "🧬": "heroicons:beaker",
  "🌿": "heroicons:sparkles",
  "🍃": "heroicons:sparkles",
  "🛒": "heroicons:shopping-cart",
  "🛍️": "heroicons:shopping-bag",
  "💳": "heroicons:credit-card",
  "💰": "heroicons:banknotes",
  "💵": "heroicons:banknotes",
  "💎": "heroicons:sparkles",
  "📦": "heroicons:cube",
  "🚚": "heroicons:truck",
};

const resolveServiceIcon = (icon) => {
  if (typeof icon === "string" && icon.includes(":")) return icon; // already an icon name
  return SERVICE_EMOJI_ICON_MAP[icon] || "heroicons:sparkles";
};

const getPlatformColor = () => {
  // Monochrome brass — the system palette uses a single accent so every social
  // tile stays on-brand instead of falling back to rainbow brand colours.
  return "#c2a068";
};

// Load profile data from API
const loadProfileData = async () => {
  loading.value = true;
  try {
    // Get NFC card identifier from route params or query
    // Nuxt 3 catch-all [...slug] returns array of URL path segments after /profile/
    //   Pattern A (legacy single):      /profile/NFC-C65EU                   → slug = ["NFC-C65EU"]
    //   Pattern B (short pretty 2-seg): /profile/nicole-tan/1                → slug = ["nicole-tan","1"]
    //   Pattern C (long pretty 3-seg):  /profile/nicole-tan/1/userid-87      → slug = ["nicole-tan","1","userid-87"]
    // Backend resolves the encoded form using "__" as separator so we map
    // every "/" in the route param to "__" before talking to the API layer.
    const slugArr = route.params.slug;
    let rawId;
    if (Array.isArray(slugArr) && slugArr.length > 0) {
      rawId = slugArr.filter(Boolean).join('/');
    } else {
      rawId = route.params.id;
    }
    if (!rawId) rawId = route.query.nfc_card_id || route.query.id;
    if (typeof rawId === 'string' && rawId.includes('/')) {
      const segs = rawId.split('/').filter(Boolean);
      if (segs.length === 2 || segs.length === 3) {
        rawId = segs.join('__');
      } else if (segs.length === 1) {
        rawId = segs[0];
      }
    }
    const nfcCardId = rawId;

    if (!nfcCardId) {
      console.error("No NFC card ID provided");
      profileNotFound.value = true;
      loading.value = false;
      return;
    }

    // Store the current NFC card ID
    currentNfcCardId.value = nfcCardId;

    // Check if in preview mode
    const previewMode = route.query.preview === 'true';
    isPreviewMode.value = previewMode;
    
    // Try to load preview data from localStorage if in preview mode
    if (previewMode) {
      try {
        const previewDataStr = localStorage.getItem('nfc_preview_data');
        if (previewDataStr) {
          const previewData = JSON.parse(previewDataStr);
          
          // Check if data is fresh (within 5 minutes)
          if (Date.now() - previewData.timestamp < 5 * 60 * 1000) {
            console.log('Loading preview data from localStorage');
            loadPreviewData(previewData);
            loading.value = false;
            return;
          }
        }
      } catch (error) {
        console.error('Failed to load preview data:', error);
      }
    }

    // Fetch landing page data for this NFC card
    console.log(`Fetching landing page for NFC card: ${nfcCardId}`);
    console.log(`Request URL: /nfc-cards/${nfcCardId}/landing-page`);
    const response = await $api.get(`/nfc-cards/${nfcCardId}/landing-page`);
    console.log('API Response:', response);
    console.log('Landing Page Data:', response.landing_page);
    console.log('Has data?', !!response.landing_page);

    if (response.success && response.landing_page) {
      const data = response.landing_page;
      console.log('Loading landing page data...');

      // Basic Info (Profile Tab)
      profile.value.name = data.name || "Your Name";
      profile.value.qualification = data.qualification || "";
      profile.value.position = data.title || data.position || "Your Position";
      profile.value.pronouns = data.pronouns || "";
      profile.value.tagline = data.tagline || "";
      profile.value.bio = data.bio || "";
      profile.value.phone = data.phone || data.phone_number || "";
      profile.value.email = data.email || data.email_address || "";
      profile.value.website = data.website || data.website_url || "";
      profile.value.address = data.address || "";
      profile.value.education = data.education || [];
      profile.value.certifications = data.certifications || [];
      profile.value.profileStats = data.profile_stats || data.profileStats || [];

      // Cover Banner
      if (data.cover_banner || data.coverBanner) {
        coverBanner.value = data.cover_banner || data.coverBanner;
      }

      // Profile Image - priority: profile_image > profilePicture > image > initials
      if (data.profile_image || data.profilePicture || data.image) {
        profileImage.value = data.profile_image || data.profilePicture || data.image;
      } else if (data.name) {
        // Generate initials from name
        const initials = data.name
          .split(" ")
          .map((n) => n[0])
          .join("")
          .substring(0, 2)
          .toUpperCase();
        profileImage.value = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23c2a068'/%3E%3Ctext x='75' y='95' font-size='60' fill='white' text-anchor='middle' font-family='Arial' font-weight='bold'%3E${initials}%3C/text%3E%3C/svg%3E`;
      }

      // Company Info
      if (data.company_logo) {
        company.value.logo = data.company_logo;
      }
      company.value.logoText = data.company_logo_text || "CO";
      company.value.name = data.company_name || "Company Name";
      company.value.registrationNo = data.company_registration_no || "";
      company.value.department = data.company_department || "";
      company.value.description = data.company_description || "";
      company.value.industry = data.industry || "";
      company.value.establishedYear = data.established_year || "";
      company.value.employeeCount = data.employee_count || "";

      // Address Info (new 3-line format first, then legacy fallback)
      address.value.line1 = data.address_1 || data.address1 || "";
      address.value.line2 = data.address_2 || data.address2 || "";
      address.value.line3 = data.address_3 || data.address3 || "";

      // Legacy separate address fields - AVOID duplicate display if new 3-line format is present
      const hasLiveNewFormat = !!(address.value.line1 || address.value.line2 || address.value.line3);
      if (hasLiveNewFormat) {
        const newAllLive = [address.value.line1, address.value.line2, address.value.line3].filter(Boolean);
        if (!data.address_street && !data.address_name && newAllLive.length > 0) {
          address.value.street = newAllLive[0];
          if (newAllLive[1]) address.value.area = newAllLive[1];
          if (newAllLive[2]) {
            const m2 = newAllLive[2].match(/^(\d{4,6})\s+(.+)$/);
            if (m2) {
              address.value.postalCode = m2[1];
              address.value.cityState = m2[2];
            } else {
              address.value.cityState = newAllLive[2];
            }
          }
        } else {
          address.value.name = data.address_name || "";
          address.value.street = data.address_street || (data.address ? data.address : "");
          address.value.area = data.address_area || "";
          address.value.cityState = data.address_city_state || "";
          address.value.country = data.address_country || "";
          address.value.postalCode = data.postal_code || "";
        }
      } else {
        address.value.name = data.address_name || "";
        address.value.street = data.address_street || (data.address ? data.address : "");
        address.value.area = data.address_area || "";
        address.value.cityState = data.address_city_state || "";
        address.value.country = data.address_country || "";
        address.value.postalCode = data.postal_code || "";
      }
      address.value.mapUrl = data.address_map_url || data.map_url || "";

      // Stats
      if (data.stats && Array.isArray(data.stats) && data.stats.length > 0) {
        stats.value = data.stats;
      }

      // Services
      if (
        data.services &&
        Array.isArray(data.services) &&
        data.services.length > 0
      ) {
        services.value = data.services.map(service => ({
          ...service,
          name: service.name || service.service_name || service.serviceName,
          icon: service.icon || "heroicons:rocket-launch",
          category: service.category || service.service_category || service.serviceCategory || "",
          linkType: service.link_type || service.serviceLinkType || service.service_link_type || "Description & Features",
          url: service.url || service.serviceUrl || service.service_url || "",
          duration: service.duration || service.service_duration || service.serviceDuration || "",
          description: service.description || service.service_description || service.serviceDescription || "",
          features: service.features || service.service_features || service.serviceFeatures || [],
          tags: service.tags || service.service_tags || service.serviceTags || [],
          price: service.price || service.service_price || service.servicePrice || "",
          oldPrice: service.old_price || service.oldPrice || service.service_old_price || service.serviceOldPrice || "",
          gallery: service.gallery || service.service_gallery || service.images || [],
          video: service.video || service.service_video || service.promo_video || "",
          brochure: service.brochure || service.service_brochure || "",
          bookingUrl: service.booking_url || service.bookingUrl || "",
          bookingEnabled: service.booking_enabled !== undefined ? service.booking_enabled : (service.bookingEnabled !== undefined ? service.bookingEnabled : false)
        }));
      }

      // Contact Methods
      // Phone - with fallback to Basic Info phone
      if (data.phone_number) {
        contactMethods.value[0].subtitle = data.phone_number;
        contactMethods.value[0].href = `tel:${data.phone_number.replace(
          /\s/g,
          ""
        )}`;
        contactMethods.value[0].label = data.phone_label || "Phone";
      } else if (data.phone) {
        // Fallback to Basic Info phone
        contactMethods.value[0].subtitle = data.phone;
        contactMethods.value[0].href = `tel:${data.phone.replace(
          /\s/g,
          ""
        )}`;
        contactMethods.value[0].label = "Phone";
      }

      if (data.email_address || data.email) {
        const email = data.email_address || data.email;
        contactMethods.value[1].subtitle = email;
        contactMethods.value[1].href = `mailto:${email}`;
        contactMethods.value[1].label = data.email_label || "Email";
      }

      if (data.whatsapp_number) {
        contactMethods.value[2].subtitle = "Chat with me";
        contactMethods.value[2].href = `https://wa.me/${data.whatsapp_number.replace(
          /\D/g,
          ""
        )}`;
        contactMethods.value[2].label = data.whatsapp_label || "WhatsApp";
      }

      if (data.website_url || data.website) {
        const websiteUrl = data.website_url || data.website;
        contactMethods.value[3].subtitle = websiteUrl.replace(
          /^https?:\/\//,
          ""
        );
        contactMethods.value[3].href = websiteUrl;
        contactMethods.value[3].label = data.website_label || "Website";
      }
      // Reset contact address index to avoid stale values
      contactMethods.value[4].subtitle = "";
      contactMethods.value[4].href = "";
      contactMethods.value[4].label = "";

      // Contact Address - use profile contact address for "Get In Touch" section
      // DEDUP: Only show if DIFFERENT from Our Location section (avoid double display)
      const contactAddress = data.address || '';
      if (contactAddress) {
        const liveLocationFlat = ([address.value.line1, address.value.line2, address.value.line3,
          address.value.street, address.value.area, address.value.postalCode, address.value.cityState, address.value.country])
          .filter(Boolean).join(", ").toLowerCase().replace(/\s+/g, " ");
        const liveContactFlat = contactAddress.toLowerCase().replace(/\s+/g, " ").trim();
        const isLiveDuplicate = liveContactFlat.length > 10 && liveLocationFlat.length > 10 &&
          (liveContactFlat === liveLocationFlat || liveLocationFlat.includes(liveContactFlat) || liveContactFlat.includes(liveLocationFlat) ||
            liveContactFlat.substring(0, 50) === liveLocationFlat.substring(0, 50));
        if (!isLiveDuplicate) {
          contactMethods.value[4].subtitle = contactAddress;
          contactMethods.value[4].href = `https://maps.google.com/?q=${encodeURIComponent(contactAddress)}`;
          contactMethods.value[4].label = "Contact Address";
        }
      }

      // Social Links - now from socialLinks relationship (social_links table)
      if (
        data.social_links &&
        Array.isArray(data.social_links) &&
        data.social_links.length > 0
      ) {
        socialLinks.value = data.social_links
          .filter((link) => link.is_active && link.url) // Only show active links
          .sort((a, b) => (a.order || 0) - (b.order || 0)) // Sort by order
          .map((link) => ({
            emoji: getPlatformIcon(link.platform),
            name: link.title || link.platform,
            href: link.url,
            color: getPlatformColor(link.platform),
            id: link.id, // Track for click analytics
          }));
      }

      // Team Members
      if (
        data.team_members &&
        Array.isArray(data.team_members) &&
        data.team_members.length > 0
      ) {
        teamMembers.value = data.team_members.filter((member) => member.name);
      }

      // ==================== B. LOAD SUPPLEMENTARY FIELDS ====================
      // Working Hours
      if (data.working_hours && Array.isArray(data.working_hours)) {
        workingHours.value = data.working_hours;
      }

      // Expertise & Skills
      if (data.expertise && Array.isArray(data.expertise)) {
        expertise.value = data.expertise;
      }

      // Awards
      if (data.awards && Array.isArray(data.awards)) {
        awards.value = data.awards;
      }

      // Gallery
      if (data.gallery && Array.isArray(data.gallery)) {
        gallery.value = data.gallery;
      }

      // Portfolio / Projects
      if (data.projects && Array.isArray(data.projects)) {
        portfolio.value = data.projects.map(project => ({
          ...project,
          title: project.title || project.project_title || project.name || "",
          description: project.description || project.project_details || "",
          cover_image: project.cover_image || project.coverImage || project.image || "",
          gallery: project.gallery || project.project_gallery || project.images || [],
          client_name: project.client_name || project.clientName || "",
          location: project.location || "",
          date_completed: project.date_completed || project.dateCompleted || project.completion_date || "",
          url: project.url || project.project_url || project.external_link || project.externalLink || "",
          pdf_download: project.pdf_download || project.pdfDownload || project.brochure || "",
          skills_used: project.skills_used || project.skillsUsed || [],
          tags: project.tags || [],
          category: project.category || project.portfolio_category || "",
        }));
      } else if (data.portfolio && Array.isArray(data.portfolio)) {
        portfolio.value = data.portfolio.map(project => ({
          ...project,
          title: project.title || project.project_title || project.name || "",
          description: project.description || project.project_details || "",
          cover_image: project.cover_image || project.coverImage || project.image || "",
          gallery: project.gallery || project.project_gallery || project.images || [],
          client_name: project.client_name || project.clientName || "",
          location: project.location || "",
          date_completed: project.date_completed || project.dateCompleted || project.completion_date || "",
          url: project.url || project.project_url || project.external_link || project.externalLink || "",
          pdf_download: project.pdf_download || project.pdfDownload || project.brochure || "",
          skills_used: project.skills_used || project.skillsUsed || [],
          tags: project.tags || [],
          category: project.category || project.portfolio_category || "",
        }));
      }

      // Blog Posts
      if (data.blog_posts && Array.isArray(data.blog_posts)) {
        blogPosts.value = data.blog_posts;
      }

      // Service Details
      if (data.service_features && Array.isArray(data.service_features)) {
        serviceDetails.value.features = data.service_features;
      }
      serviceDetails.value.price = data.service_price || "";
      serviceDetails.value.oldPrice = data.service_old_price || "";
      serviceDetails.value.duration = data.service_duration || "";
      if (data.service_tags && Array.isArray(data.service_tags)) {
        serviceDetails.value.tags = data.service_tags;
      }
      if (data.service_brochure) {
        serviceDetails.value.brochure = data.service_brochure;
      }
      serviceDetails.value.bookingEnabled = data.booking_enabled || false;
      serviceDetails.value.bookingUrl = data.booking_url || "";

      // Payment Button
      if (data.payment_button_text && data.payment_button_url) {
        paymentButton.value.text = data.payment_button_text;
        paymentButton.value.url = data.payment_button_url;
      }

      // Appointment Link
      if (data.appointment_link) {
        appointmentLink.value = data.appointment_link;
      }

      // Use profileStats if available, otherwise fall back to stats
      if (profile.value.profileStats && profile.value.profileStats.length > 0) {
        stats.value = profile.value.profileStats;
      } else if (data.stats && Array.isArray(data.stats) && data.stats.length > 0) {
        stats.value = data.stats;
      }

      // ==================== A. LOAD SECTION LAYOUT ====================
      if (data.section_layout && Array.isArray(data.section_layout)) {
        sectionLayout.value = data.section_layout;
        console.log('Section layout loaded:', sectionLayout.value);
      }
      
      // ==================== B. LOAD FIELD LAYOUT (with sub-section states) ====================
      if (data.field_layout && typeof data.field_layout === 'object') {
        fieldLayout.value = data.field_layout;
        console.log('Field layout loaded:', fieldLayout.value);
      }

      // ==================== C. LOAD FEATURE SETTINGS ====================
      // Load user feature toggles first (needed to derive availableFeatures below)
      if (data.features && typeof data.features === 'object') {
        features.value = { ...features.value, ...data.features };
      }
      // Server-resolved on/off for the public actions (plan + admin toggle +
      // owner switch). Authoritative over the raw saved flags above.
      if (response.effective_features && typeof response.effective_features === 'object') {
        features.value = { ...features.value, ...response.effective_features };
      }
      introVideoEmbed.value = response.videos?.intro || null;
      companyVideoEmbed.value = response.videos?.company || null;

      // Branding visibility is resolved server-side (plan + admin policy).
      showNfcgoBranding.value = typeof response.show_nfcgo_branding === 'boolean'
        ? response.show_nfcgo_branding
        : features.value.remove_branding !== true;

      // Load available features (assigned by admin)
      // Backend does not persist available_features, so derive it from the user's
      // feature toggles. Any feature the user has a setting for is "available".
      const knownFeatureKeys = ['contact_form', 'vcard_download', 'qr_code', 'remove_branding', 'booking_integration', 'social_sharing'];
      if (data.available_features && Array.isArray(data.available_features) && data.available_features.length > 0) {
        availableFeatures.value = data.available_features;
        console.log('Available features (from API):', availableFeatures.value);
      } else {
        const derivedFromFeatures = Object.keys(features.value).filter(k => knownFeatureKeys.includes(k));
        availableFeatures.value = derivedFromFeatures.length > 0 ? derivedFromFeatures : knownFeatureKeys;
        console.log('Available features (derived):', availableFeatures.value);
      }

      if (data.feature_order && Array.isArray(data.feature_order)) {
        featureOrder.value = data.feature_order;
        console.log('Feature order loaded:', featureOrder.value);
      }

      // === Apply design_config (complete design object) ===
      if (data.design_config) {
        console.log('Applying design config:', data.design_config);
        applyDesignConfig(data.design_config);
      }
      
      // === Apply direct design fields (backward compatibility) ===
      applyDirectDesignFields(data);

      // === Final colours resolved by the server (theme + plan) ===
      applyResolvedDesign(response.design);

      // === Use visible_fields to control display ===
      if (data.visible_fields) {
        console.log('Visible fields config:', data.visible_fields);
        visibleFieldsConfig.value = data.visible_fields;
      }

      profileNotFound.value = false;
      noProfileData.value = false;
      
      // Check if current user owns this card (to show Edit button)
      await checkCardOwnership();
    } else {
      console.warn('No landing page data returned');
      noProfileData.value = true;
    }
  } catch (error) {
    // 404 = Card exists but has no landing page data yet
    if (error.response?.status === 404) {
      console.log('No landing page data found for this card (expected for new cards)');
      noProfileData.value = true;
      // Check if user owns this card to show setup button
      await checkCardOwnership();
    } else {
      // Other errors = Card doesn't exist or server error
      console.error("Error loading landing page:", error);
      profileNotFound.value = true;
    }
  } finally {
    // The owner's theme is honoured (see applyResolvedDesign); the page no
    // longer forces "Ink & Brass" on every profile.
    loading.value = false;
  }
};

// Added: Function to apply design configuration
const applyDesignConfig = (designConfig) => {
  try {
    console.log('Applying design config:', designConfig);
    
    // Apply Theme
    if (designConfig.theme) {
      const theme = designConfig.theme;
      designSettings.theme = theme.id || 'default';
      if (theme.backgroundColor) {
        designSettings.backgroundColor = theme.backgroundColor;
      }
      if (theme.textColor) designSettings.textColor = theme.textColor;
      if (theme.accentColor) designSettings.accentColor = theme.accentColor;
      if (theme.bgGradient) designSettings.bgGradient = theme.bgGradient;
    }

    // Apply Font
    if (designConfig.font) {
      const font = designConfig.font;
      if (font.family) {
        designSettings.fontFamily = font.family;
      }
    }

    // Apply Card Style & Background Animation
    if (designConfig.cardStyle) {
      designSettings.cardStyle = designConfig.cardStyle.id || designConfig.cardStyle;
    }
    if (designConfig.bgAnimation) {
      designSettings.bgAnimation = designConfig.bgAnimation.id || designConfig.bgAnimation;
    }
    if (designConfig.cardEffectColor) {
      designSettings.cardEffectColor = designConfig.cardEffectColor;
    }
    if (designConfig.cardEffectOpacity !== undefined && designConfig.cardEffectOpacity !== null) {
      designSettings.cardEffectOpacity = Number(designConfig.cardEffectOpacity);
    }

    // Apply Button Style
    if (designConfig.buttonStyle) {
      const buttonStyle = designConfig.buttonStyle;
      designSettings.buttonStyle = buttonStyle.id || 'solid';
      if (buttonStyle.class) {
        buttonClasses.value = buttonStyle.class;
      }
    }

    // Apply Color Scheme
    if (designConfig.colorScheme && designConfig.colorScheme.colors) {
      const colors = designConfig.colorScheme.colors;
      if (colors.background) designSettings.backgroundColor = colors.background;
      if (colors.text) designSettings.textColor = colors.text;
    }

    // Save current theme configuration
    currentThemeConfig.value = designConfig;
    
    console.log('Design settings applied:', designSettings);
  } catch (error) {
    console.error('Error applying design config:', error);
  }
};

// Apply direct design fields (from landing_page table)
const applyDirectDesignFields = (data) => {
  if (data.background_color) {
    designSettings.backgroundColor = data.background_color;
  }
  if (data.text_color) {
    designSettings.textColor = data.text_color;
  }
  if (data.accent_color || data.accentColor) {
    designSettings.accentColor = data.accent_color || data.accentColor;
  }
  if (data.bg_gradient || data.bgGradient) {
    designSettings.bgGradient = data.bg_gradient || data.bgGradient;
  }
  if (data.card_effect_color || data.cardEffectColor) {
    designSettings.cardEffectColor = data.card_effect_color || data.cardEffectColor;
  }
  if (data.card_effect_opacity !== undefined && data.card_effect_opacity !== null) {
    designSettings.cardEffectOpacity = Number(data.card_effect_opacity);
  } else if (data.cardEffectOpacity !== undefined && data.cardEffectOpacity !== null) {
    designSettings.cardEffectOpacity = Number(data.cardEffectOpacity);
  }
  if (data.card_style || data.cardStyle) {
    designSettings.cardStyle = data.card_style || data.cardStyle;
  }
  if (data.bg_animation || data.bgAnimation) {
    designSettings.bgAnimation = data.bg_animation || data.bgAnimation;
  }
  if (data.font) {
    // Map font ID to font family
    const fontMap = {
      'inter': 'Inter, sans-serif',
      'roboto': 'Roboto, sans-serif',
      'playfair': 'Playfair Display, serif',
      'poppins': 'Poppins, sans-serif',
      'montserrat': 'Montserrat, sans-serif',
      'lora': 'Lora, serif',
      'nunito': 'Nunito, sans-serif',
    };
    designSettings.fontFamily = fontMap[data.font] || data.font;
  }
  if (data.button_style) {
    designSettings.buttonStyle = data.button_style;
    // Map button style to classes
    const buttonStyleMap = {
      'solid': 'bg-black text-white rounded-full',
      'outline': 'border-2 border-black text-black rounded-full bg-transparent',
      'soft': 'bg-gray-100 text-gray-900 rounded-xl',
      'shadow': 'bg-white text-black rounded-xl shadow-lg',
    };
    buttonClasses.value = buttonStyleMap[data.button_style] || '';
  }
};

// Added: Check if field should be displayed
const shouldShowField = (fieldKey) => {
  if (!visibleFieldsConfig.value || visibleFieldsConfig.value.length === 0) {
    return true; // If no configuration, show all fields by default
  }
  
  return visibleFieldsConfig.value.some(f => f.field_key === fieldKey);
};

// ==================== C. Handle Actions (using real data) ====================
const handleAction = (action) => {
  const actions = {
    call: () => {
      if (contactMethods.value[0]?.href) {
        window.location.href = contactMethods.value[0].href;
      }
    },
    email: () => {
      if (contactMethods.value[1]?.href) {
        window.location.href = contactMethods.value[1].href;
      }
    },
    whatsapp: () => {
      if (contactMethods.value[2]?.href) {
        window.open(contactMethods.value[2].href, "_blank");
      }
    },
    appointment: () => {
      if (appointmentLink.value) {
        window.open(appointmentLink.value, "_blank");
      }
    },
    payment: () => {
      if (paymentButton.value.url) {
        window.open(paymentButton.value.url, "_blank");
      }
    },
    website: () => {
      if (contactMethods.value[3]?.href) {
        window.open(contactMethods.value[3].href, "_blank");
      }
    },
  };
  actions[action]?.();
};

const handleCardTilt = (e) => {
  if (!companyCardRef.value) return;
  const card = companyCardRef.value;
  const rect = card.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  const centerX = rect.width / 2;
  const centerY = rect.height / 2;
  const rotateX = (y - centerY) / 10;
  const rotateY = (centerX - x) / 10;
  card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
};

const resetCardTilt = () => {
  if (companyCardRef.value) {
    companyCardRef.value.style.transform =
      "perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)";
  }
};

const escapeVCard = (value) => {
  if (value == null) return '';
  return String(value)
    .replace(/\\/g, '\\\\')
    .replace(/;/g, '\\;')
    .replace(/,/g, '\\,')
    .replace(/\n/g, '\\n');
};

const saveContact = () => {
  const name = profile.value.name || '';
  const nameParts = name.split(' ').filter(Boolean);
  const lastName = nameParts.length > 1 ? nameParts[nameParts.length - 1] : '';
  const firstName = nameParts.length > 1 ? nameParts.slice(0, -1).join(' ') : name;

  const lines = ['BEGIN:VCARD', 'VERSION:3.0'];

  if (name) lines.push(`FN:${escapeVCard(name)}`);
  if (lastName || firstName) lines.push(`N:${escapeVCard(lastName)};${escapeVCard(firstName)};;;`);
  if (company.value.name) lines.push(`ORG:${escapeVCard(company.value.name)}`);
  if (profile.value.position) lines.push(`TITLE:${escapeVCard(profile.value.position)}`);
  if (contactMethods.value[0]?.subtitle) lines.push(`TEL;TYPE=CELL:${escapeVCard(contactMethods.value[0].subtitle)}`);
  if (contactMethods.value[1]?.subtitle) lines.push(`EMAIL:${escapeVCard(contactMethods.value[1].subtitle)}`);
  if (contactMethods.value[3]?.href) lines.push(`URL:${escapeVCard(contactMethods.value[3].href)}`);

  const addrParts = [address.value.street, address.value.area].filter(Boolean).join(', ');
  if (addrParts || address.value.cityState || address.value.country) {
    lines.push(`ADR;TYPE=WORK:;;${escapeVCard(addrParts)};${escapeVCard(address.value.cityState || '')};;${escapeVCard(address.value.country || '')}`);
  }

  const noteParts = [profile.value.qualification, company.value.department].filter(Boolean);
  if (noteParts.length > 0) lines.push(`NOTE:${escapeVCard(noteParts.join(' - '))}`);

  lines.push('END:VCARD');

  const vCard = lines.join('\n');
  const blob = new Blob([vCard], { type: 'text/vcard;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `${(name || 'contact').toLowerCase().replace(/\s+/g, '-')}-contact.vcf`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

// Current page URL for QR Code
const currentUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.href;
  }
  return '';
});

// ============ SEO / OPEN GRAPH ============
// Meta is derived from already-loaded profile data and stays reactive so it
// updates once loadProfileData()/loadPreviewData() resolve. The description
// never exposes the private contact fields (email/phone).
const seoTitle = computed(() => {
  const name = (profile.value.name || "").trim();
  if (!name) return "NFCGo Digital Business Card";
  const role = (profile.value.position || "").trim();
  const comp = (company.value.name || "").trim();
  const suffix = role && comp ? `${role} at ${comp}` : role || comp;
  return suffix ? `${name} - ${suffix}` : `${name} | NFCGo Digital Business Card`;
});

const seoDescription = computed(() => {
  const bio = (profile.value.bio || profile.value.tagline || "").trim();
  if (bio) return bio.length > 160 ? `${bio.slice(0, 157)}...` : bio;
  const name = (profile.value.name || "").trim();
  return name
    ? `View the digital business card of ${name} on NFCGo. Connect instantly by tapping an NFC card.`
    : "NFCGo digital business card - tap to connect and share your professional profile instantly.";
});

// Only surface a real, user-supplied image URL. The default avatar is an inline
// SVG data-URI placeholder, which must never be emitted as an og:image.
const seoImage = computed(() => {
  const img = profileImage.value;
  return typeof img === "string" && img && !img.startsWith("data:")
    ? img
    : undefined;
});

useSeoMeta({
  title: seoTitle,
  description: seoDescription,
  ogTitle: seoTitle,
  ogDescription: seoDescription,
  ogType: "profile",
  ogUrl: currentUrl,
  ogImage: seoImage,
  twitterCard: "summary_large_image",
  twitterTitle: seoTitle,
  twitterDescription: seoDescription,
  twitterImage: seoImage,
});

// Copy profile link to clipboard
const copyProfileLink = async () => {
  try {
    await navigator.clipboard.writeText(currentUrl.value);
    // Show success feedback (could use a toast here)
    alert('Profile link copied to clipboard!');
  } catch (err) {
    console.error('Failed to copy:', err);
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = currentUrl.value;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    alert('Profile link copied to clipboard!');
  }
};

// Native share (Web Share API) with graceful fallback to copy
const shareProfile = async () => {
  const shareData = {
    title: seoTitle.value,
    text: seoDescription.value,
    url: currentUrl.value,
  };
  try {
    if (typeof navigator !== 'undefined' && typeof navigator.share === 'function') {
      await navigator.share(shareData);
      return;
    }
  } catch (err) {
    // User dismissed the native share sheet — no fallback required
    if (err && err.name === 'AbortError') return;
  }
  await copyProfileLink();
};

// Submit contact form
const submitContactForm = async () => {
  try {
    contactFormSubmitting.value = true;
    
    // Get the NFC card ID from the already-resolved loaded identifier (NOT raw route param,
    // because pretty URL slugs like "nicole-tan/1" are NOT actual NFC card identifiers).
    // currentNfcCardId.value is set in loadProfileData() above to the encoded identifier the
    // API resolver successfully used, so it's guaranteed to be PK / nfc_card_id / phone /
    // encoded-slug-n form that backend routes understand.
    const cardId = currentNfcCardId.value;
    
    // Send the contact form data to backend
    // POST /api/nfc-cards/{card}/contact (NfcCardController::submitContactForm).
    // The old /contact-form route never existed, and axios ignores `body`.
    await $api.post(`/nfc-cards/${encodeURIComponent(cardId)}/contact`, {
      name: contactForm.value.name,
      email: contactForm.value.email,
      phone: contactForm.value.phone,
      message: contactForm.value.message,
    });
    
    // Show success state
    contactFormSuccess.value = true;
    
    // Reset form after 3 seconds and close modal
    setTimeout(() => {
      contactForm.value = { name: '', email: '', phone: '', message: '' };
      contactFormSuccess.value = false;
      showContactForm.value = false;
    }, 3000);
    
  } catch (error) {
    console.error('Error submitting contact form:', error);
    alert(error?.data?.message || 'Failed to send message. Please try again.');
  } finally {
    contactFormSubmitting.value = false;
  }
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 300;

  // Show home button while scrolling
  showHomeButton.value = true;

  // Clear existing timeout
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }

  // Hide button after 1 second of no scrolling
  scrollTimeout = setTimeout(() => {
    showHomeButton.value = false;
  }, 1000);
};

const handleResize = () => {
  if (resizeDebounce) clearTimeout(resizeDebounce);
  resizeDebounce = setTimeout(() => {
    windowWidth.value = window.innerWidth;
  }, 50);
};

// Hover handlers for desktop-only interactions
const handleHoverTransform = (e, transform) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = transform;
  }
};

const handleHoverStyle = (e, styles) => {
  if (!isMobile.value) {
    Object.assign(e.currentTarget.style, styles);
  }
};

const handleContactHoverIn = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateX(8px)";
    e.currentTarget.style.background = hexToRgba(designSettings.accentColor, 0.06);
    e.currentTarget.style.borderBottomColor = hexToRgba(designSettings.accentColor, 0.5);
  }
};

const handleContactHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateX(0)";
    e.currentTarget.style.background = "transparent";
    e.currentTarget.style.borderBottomColor = hexToRgba(designSettings.accentColor, 0.16);
  }
};

const handleMapButtonHoverIn = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "scale(1.02)";
  }
};

const handleMapButtonHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "scale(1)";
  }
};

const handleSocialHoverIn = (e, color) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(-5px)";
    e.currentTarget.style.borderColor = color;
  }
};

const handleSocialHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(0)";
    e.currentTarget.style.borderColor = "rgba(255, 255, 255, 0.1)";
  }
};

const handleVCardHoverIn = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(-3px)";
    e.currentTarget.style.boxShadow = "0 15px 40px rgba(194, 160, 104, 0.5)";
  }
};

const handleVCardHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(0)";
    e.currentTarget.style.boxShadow = "none";
  }
};

const handleScrollButtonHoverIn = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(-5px)";
    e.currentTarget.style.boxShadow = "0 15px 40px rgba(194, 160, 104, 0.6)";
  }
};

const handleScrollButtonHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(0)";
    e.currentTarget.style.boxShadow = "0 10px 30px rgba(194, 160, 104, 0.4)";
  }
};

const handleBackButtonHoverIn = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateX(-3px)";
    e.currentTarget.style.background = "rgba(255, 255, 255, 0.15)";
    e.currentTarget.style.boxShadow = "0 6px 20px rgba(0, 0, 0, 0.3)";
  }
};

const handleBackButtonHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateX(0)";
    e.currentTarget.style.background = "rgba(255, 255, 255, 0.1)";
    e.currentTarget.style.boxShadow = "0 4px 15px rgba(0, 0, 0, 0.2)";
  }
};

const handleEditButtonHoverIn = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(-3px)";
    e.currentTarget.style.boxShadow = "0 8px 25px rgba(194, 160, 104, 0.6)";
  }
};

const handleEditButtonHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(0)";
    e.currentTarget.style.boxShadow = "0 4px 15px rgba(194, 160, 104, 0.4)";
  }
};

const goToHome = () => {
  navigateTo("/");
};

const goToEditProfile = () => {
  // Navigate to Business Profile Builder with the current NFC card ID
  navigateTo(`/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder?cardId=${currentNfcCardId.value}`);
};

// Check if current user owns this NFC card
const checkCardOwnership = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token || !currentNfcCardId.value) {
      showEditButton.value = false;
      return;
    }

    // Get current user info
    const userResponse = await $api.get('/me');
    if (!userResponse.success) {
      showEditButton.value = false;
      return;
    }

    // Get NFC card details to check ownership
    const cardResponse = await $api.get(`/nfc-cards/${currentNfcCardId.value}`);
    if (cardResponse.success && cardResponse.nfc_card) {
      isCardOwner.value = cardResponse.nfc_card.user_id === userResponse.user.id;
      showEditButton.value = isCardOwner.value;
    }
  } catch (error) {
    console.log('Not authenticated or error checking ownership:', error);
    showEditButton.value = false;
  }
};

// Lifecycle
onMounted(() => {
  window.addEventListener("scroll", handleScroll, { passive: true });
  window.addEventListener("resize", handleResize);
  handleResize(); // Initialize on mount
  loadProfileData(); // Load profile data from API
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll, { passive: true });
  window.removeEventListener("resize", handleResize);

  // Clear timeout to prevent memory leaks
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }
});
</script>

<style scoped>
/* No scoped styles needed - all styles are inline */
@keyframes fireflyFloat { 0%{transform:translate3d(0,0,0) scale(1);} 50%{transform:translate3d(14px,-22px,0) scale(1.1);} 100%{transform:translate3d(-10px,12px,0) scale(0.95);} }
@keyframes liquidDriftA { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0);} 33%{transform:translate3d(18px,-26px,0) scale(1.08) rotate(12deg);} 66%{transform:translate3d(-14px,14px,0) scale(0.94) rotate(-10deg);} }
@keyframes liquidDriftB { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0);} 33%{transform:translate3d(-22px,18px,0) scale(1.06) rotate(-14deg);} 66%{transform:translate3d(12px,-16px,0) scale(0.96) rotate(8deg);} }
@keyframes snowFall { 0%{transform:translate3d(-4px,-10vh,0) rotate(0);opacity:0;} 10%{opacity:.9;} 90%{opacity:.9;} 100%{transform:translate3d(14px,115vh,0) rotate(180deg);opacity:0;} }
@keyframes bouncePulse { 0%,100%{transform:rotate(45deg) scale(1);} 50%{transform:rotate(135deg) scale(1.25);} }
</style>
