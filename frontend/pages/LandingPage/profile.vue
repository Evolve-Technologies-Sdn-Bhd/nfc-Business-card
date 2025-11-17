<template>
  <div
    :style="{
      minHeight: '100vh',
      background: '#0a0e27',
      position: 'relative',
      overflow: 'hidden',
      fontFamily:
        'Inter, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif',
    }"
  >
    <!-- Animations -->
    <component :is="'style'">
      {{
        `
        @keyframes float { 0%, 100% { transform: translate(0, 0) scale(1); } 33% { transform: translate(100px, -100px) scale(1.1); } 66% { transform: translate(-100px, 100px) scale(0.9); } }
        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulse-status { 0%, 100% { box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7); } 50% { box-shadow: 0 0 0 10px rgba(0, 255, 136, 0); } }
        @keyframes charBounce { 0% { opacity: 0; transform: translateY(20px); } 50% { transform: translateY(-10px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes particleFloat { 0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; } 50% { transform: translateY(-20px) scale(1.2); opacity: 0.6; } }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes rippleEffect { to { transform: scale(2); opacity: 0; } }
        @keyframes logoRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
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
      `
      }}
    </component>

    <!-- Animated Background -->
    <div
      :style="{
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        zIndex: 0,
        overflow: 'hidden',
      }"
    >
      <div
        :style="{
          position: 'absolute',
          width: isMobile ? '300px' : '500px',
          height: isMobile ? '300px' : '500px',
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          borderRadius: '50%',
          filter: isMobile ? 'blur(60px)' : 'blur(80px)',
          opacity: 0.6,
          animation: 'float 20s infinite ease-in-out',
          top: isMobile ? '-150px' : '-250px',
          left: isMobile ? '-150px' : '-250px',
        }"
      />
      <div
        :style="{
          position: 'absolute',
          width: isMobile ? '250px' : '400px',
          height: isMobile ? '250px' : '400px',
          background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
          borderRadius: '50%',
          filter: isMobile ? 'blur(60px)' : 'blur(80px)',
          opacity: 0.6,
          animation: 'float 20s infinite ease-in-out',
          bottom: isMobile ? '-125px' : '-200px',
          right: isMobile ? '-125px' : '-200px',
          animationDelay: '7s',
        }"
      />
      <div
        :style="{
          position: 'absolute',
          width: isMobile ? '200px' : '350px',
          height: isMobile ? '200px' : '350px',
          background: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
          borderRadius: '50%',
          filter: isMobile ? 'blur(60px)' : 'blur(80px)',
          opacity: 0.6,
          animation: 'float 20s infinite ease-in-out',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          animationDelay: '14s',
        }"
      />
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
          top: isMobile ? '15px' : '20px',
          left: isMobile ? '15px' : '20px',
          zIndex: 1000,
          display: 'flex',
          alignItems: 'center',
          gap: isMobile ? '8px' : '10px',
          padding: isMobile ? '10px 15px' : '12px 20px',
          background: 'rgba(255, 255, 255, 0.1)',
          backdropFilter: 'blur(20px)',
          border: '1px solid rgba(255, 255, 255, 0.2)',
          borderRadius: isMobile ? '12px' : '15px',
          color: 'white',
          fontSize: isMobile ? '14px' : '16px',
          fontWeight: 600,
          cursor: 'pointer',
          transition: 'all 0.3s',
          boxShadow: '0 4px 15px rgba(0, 0, 0, 0.2)',
          willChange: 'opacity, transform',
        }"
      >
        <span
          :style="{ fontSize: isMobile ? '18px' : '20px' }"
          aria-hidden="true"
          >←</span
        >
        <span>Home</span>
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
          top: isMobile ? '15px' : '20px',
          right: isMobile ? '15px' : '20px',
          zIndex: 1000,
          display: 'flex',
          alignItems: 'center',
          gap: isMobile ? '8px' : '10px',
          padding: isMobile ? '10px 15px' : '12px 20px',
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          backdropFilter: 'blur(20px)',
          border: '1px solid rgba(255, 255, 255, 0.3)',
          borderRadius: isMobile ? '12px' : '15px',
          color: 'white',
          fontSize: isMobile ? '14px' : '16px',
          fontWeight: 600,
          cursor: 'pointer',
          transition: 'all 0.3s',
          boxShadow: '0 4px 15px rgba(102, 126, 234, 0.4)',
          willChange: 'opacity, transform',
        }"
      >
        <span :style="{ fontSize: isMobile ? '18px' : '20px' }" aria-hidden="true">✏️</span>
        <span>Edit Profile</span>
      </button>
    </Transition>

    <div
      :style="{
        position: 'relative',
        zIndex: 1,
        maxWidth: '1200px',
        margin: '0 auto',
        padding: responsive.containerPadding,
      }"
    >
      <!-- Loading State -->
      <div
        v-if="loading"
        :style="{ textAlign: 'center', padding: '60px 20px', color: 'white' }"
      >
        <div :style="{ fontSize: '48px', marginBottom: '20px' }">⏳</div>
        <p :style="{ fontSize: responsive.h2Size }">Loading profile...</p>
      </div>

      <!-- Profile Not Found -->
      <div
        v-else-if="profileNotFound"
        :style="{ textAlign: 'center', padding: '60px 20px', color: 'white' }"
      >
        <div :style="{ fontSize: '72px', marginBottom: '20px' }">😢</div>
        <h2 :style="{ fontSize: responsive.h2Size, marginBottom: '15px' }">
          Profile Not Found
        </h2>
        <p
          :style="{
            fontSize: responsive.bodySize,
            color: 'rgba(255, 255, 255, 0.7)',
          }"
        >
          This profile doesn't exist or has been removed.
        </p>
      </div>

      <!-- Profile Content -->
      <template v-else>
        <!-- Hero Section -->
        <div
          :style="{
            position: 'relative',
            background: 'rgba(255, 255, 255, 0.05)',
            backdropFilter: 'blur(20px)',
            border: '1px solid rgba(255, 255, 255, 0.1)',
            borderRadius: responsive.borderRadius,
            padding: isMobile ? '40px 20px' : isTablet ? '60px 30px' : '80px 40px',
            marginBottom: responsive.cardMarginBottom,
            overflow: 'hidden',
            boxShadow: '0 20px 60px rgba(0, 0, 0, 0.3)',
          }"
        >
          <div :style="{ position: 'relative', zIndex: 2, textAlign: 'center' }">
            <div
              :style="{
                position: 'relative',
                width: responsive.profileSize,
                height: responsive.profileSize,
                margin: '0 auto 40px',
                cursor: 'pointer',
              }"
              @mouseenter="imageHover = true"
              @mouseleave="imageHover = false"
            >
              <div
                :style="{
                  position: 'absolute',
                  inset: '-10px',
                  borderRadius: '50%',
                  background: 'linear-gradient(135deg, #667eea, #764ba2, #f093fb)',
                  animation: 'rotate 3s linear infinite',
                  opacity: imageHover ? 1 : 0,
                  transition: 'opacity 0.3s',
                }"
              />
              <img
                :src="profileImage"
                alt="Profile"
                :style="{
                  position: 'relative',
                  width: '100%',
                  height: '100%',
                  borderRadius: '50%',
                  border: responsive.profileBorder,
                  transition: 'transform 0.3s',
                  zIndex: 1,
                  transform: imageHover ? 'scale(1.05)' : 'scale(1)',
                }"
              />
              <div
                :style="{
                  position: 'absolute',
                  bottom: '15px',
                  right: '15px',
                  width: '20px',
                  height: '20px',
                  background: '#00ff88',
                  border: '3px solid rgba(255, 255, 255, 0.2)',
                  borderRadius: '50%',
                  zIndex: 2,
                  animation: 'pulse-status 2s infinite',
                }"
              />
            </div>

            <h1
              :style="{
                fontSize: responsive.nameSize,
                fontWeight: 800,
                color: 'white',
                marginBottom: '30px',
                letterSpacing: '2px',
              }"
            >
              <span
                v-for="(char, i) in profile.name.split('')"
                :key="i"
                :style="{
                  display: 'inline-block',
                  animation: 'charBounce 0.5s ease-out forwards',
                  opacity: 0,
                  animationDelay: `${i * 0.05}s`,
                }"
              >
                {{ char === ' ' ? '\u00A0' : char }}
              </span>
            </h1>

            <div
              :style="{
                display: 'flex',
                gap: responsive.flexGap,
                justifyContent: 'center',
                flexWrap: 'wrap',
              }"
            >
              <div
                v-if="profile.qualification"
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '10px',
                  padding: responsive.badgePadding,
                  borderRadius: '50px',
                  fontSize: responsive.smallSize,
                  fontWeight: 600,
                  backdropFilter: 'blur(10px)',
                  border: '1px solid rgba(255, 255, 255, 0.2)',
                  background: 'linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3))',
                  color: '#fff',
                  animation: 'fadeInUp 0.6s ease-out forwards',
                  opacity: 0,
                  animationDelay: '0.3s',
                }"
              >
                <span :style="{ fontSize: isMobile ? '16px' : '18px' }">🎓</span>
                {{ profile.qualification }}
              </div>
              <div
                v-if="profile.position"
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '10px',
                  padding: responsive.badgePadding,
                  borderRadius: '50px',
                  fontSize: responsive.smallSize,
                  fontWeight: 600,
                  backdropFilter: 'blur(10px)',
                  border: '1px solid rgba(255, 255, 255, 0.2)',
                  background: 'linear-gradient(135deg, rgba(240, 147, 251, 0.3), rgba(245, 87, 108, 0.3))',
                  color: '#fff',
                  animation: 'fadeInUp 0.6s ease-out forwards',
                  opacity: 0,
                  animationDelay: '0.4s',
                }"
              >
                <span :style="{ fontSize: isMobile ? '16px' : '18px' }">💼</span>
                {{ profile.position }}
              </div>
            </div>
          </div>

          <!-- Particles -->
          <div
            :style="{
              position: 'absolute',
              inset: 0,
              overflow: 'hidden',
              pointerEvents: 'none',
            }"
          >
            <div
              v-for="n in 20"
              :key="n"
              :style="{
                position: 'absolute',
                width: '4px',
                height: '4px',
                background: 'white',
                borderRadius: '50%',
                animation: 'particleFloat 3s infinite ease-in-out',
                opacity: 0.3,
                ...getParticleStyle(n),
              }"
            />
          </div>
        </div>

        <!-- Quick Actions -->
        <div
          :style="{
            display: 'flex',
            justifyContent: 'center',
            gap: responsive.actionGap,
            marginBottom: responsive.cardMarginBottom,
            flexWrap: 'wrap',
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
              gap: '8px',
              padding: responsive.actionPadding,
              minWidth: isMobile ? '70px' : '80px',
              background: 'rgba(255, 255, 255, 0.1)',
              backdropFilter: 'blur(10px)',
              border: '1px solid rgba(255, 255, 255, 0.2)',
              borderRadius: isMobile ? '15px' : '20px',
              color: 'white',
              fontWeight: 600,
              cursor: 'pointer',
              overflow: 'hidden',
              animation: 'slideInUp 0.6s ease-out backwards',
              animationDelay: `${idx * 0.1}s`,
              transition: 'all 0.3s',
            }"
          >
            <span :style="{ fontSize: responsive.actionIconSize }">{{
              action.icon
            }}</span>
            <span :style="{ fontSize: responsive.smallSize }">{{
              action.label
            }}</span>
          </button>
        </div>

        <!-- Main Content -->
        <div :style="{ display: 'grid', gap: responsive.cardMarginBottom }">
          <!-- Company Card -->
          <div
            ref="companyCardRef"
            @mousemove="!isMobile && handleCardTilt"
            @mouseleave="!isMobile && resetCardTilt"
            :style="{
              position: 'relative',
              background: 'rgba(255, 255, 255, 0.05)',
              backdropFilter: 'blur(20px)',
              border: '1px solid rgba(255, 255, 255, 0.1)',
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              transition: 'all 0.3s',
              overflow: 'hidden',
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                🏢
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
                  margin: 0,
                }"
              >
                Company
              </h2>
            </div>
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: isMobile ? '15px' : '30px',
                flexWrap: 'wrap',
              }"
            >
              <div
                :style="{
                  position: 'relative',
                  width: isMobile ? '80px' : '100px',
                  height: isMobile ? '80px' : '100px',
                  flexShrink: 0,
                }"
              >
                <div
                  :style="{
                    position: 'absolute',
                    inset: 0,
                    background: 'linear-gradient(135deg, #667eea, #764ba2)',
                    borderRadius: isMobile ? '15px' : '20px',
                    animation: 'logoRotate 10s linear infinite',
                  }"
                />
                <div
                  :style="{
                    position: 'absolute',
                    inset: '3px',
                    background: 'rgba(10, 14, 39, 0.95)',
                    borderRadius: isMobile ? '13px' : '18px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '24px' : '32px',
                    fontWeight: 800,
                    color: 'white',
                    zIndex: 1,
                  }"
                >
                  {{ company.logoText }}
                </div>
              </div>
              <div :style="{ flex: 1, minWidth: isMobile ? '100%' : 'auto' }">
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 800,
                    color: 'white',
                    marginBottom: '10px',
                  }"
                >
                  {{ company.name }}
                </h3>
                <p
                  :style="{
                    color: 'rgba(255, 255, 255, 0.6)',
                    fontSize: responsive.smallSize,
                    marginBottom: '15px',
                  }"
                >
                  📋 {{ company.registrationNo }}
                </p>
                <div
                  :style="{
                    display: 'inline-block',
                    padding: isMobile ? '8px 16px' : '10px 20px',
                    background: 'rgba(102, 126, 234, 0.2)',
                    border: '1px solid rgba(102, 126, 234, 0.4)',
                    borderRadius: '50px',
                    color: '#a8b3ff',
                    fontSize: responsive.smallSize,
                    fontWeight: 600,
                  }"
                >
                  {{ company.department }}
                </div>
              </div>
            </div>
          </div>

          <!-- About Card -->
          <div
            :style="{
              background: 'rgba(255, 255, 255, 0.05)',
              backdropFilter: 'blur(20px)',
              border: '1px solid rgba(255, 255, 255, 0.1)',
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                ✨
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
                  margin: 0,
                }"
              >
                About Me
              </h2>
            </div>
            <p
              :style="{
                color: 'rgba(255, 255, 255, 0.8)',
                lineHeight: '1.8',
                fontSize: responsive.bodySize,
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              {{ profile.bio }}
            </p>
            <div
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
                  textAlign: 'center',
                  padding: isMobile ? '15px' : '20px',
                  background: 'rgba(255, 255, 255, 0.05)',
                  borderRadius: '15px',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  transition: 'all 0.3s',
                  cursor: 'pointer',
                }"
                @mouseover="(e) => handleHoverTransform(e, 'scale(1.05)')"
                @mouseout="(e) => handleHoverTransform(e, 'scale(1)')"
              >
                <div
                  :style="{
                    fontSize: isMobile ? '28px' : '36px',
                    fontWeight: 800,
                    background: 'linear-gradient(135deg, #667eea, #f093fb)',
                    WebkitBackgroundClip: 'text',
                    WebkitTextFillColor: 'transparent',
                    marginBottom: '8px',
                  }"
                >
                  {{ stat.num }}
                </div>
                <div
                  :style="{
                    color: 'rgba(255, 255, 255, 0.6)',
                    fontSize: responsive.smallSize,
                    fontWeight: 600,
                  }"
                >
                  {{ stat.label }}
                </div>
              </div>
            </div>
          </div>

          <!-- Services -->
          <div
            :style="{
              background: 'rgba(255, 255, 255, 0.05)',
              backdropFilter: 'blur(20px)',
              border: '1px solid rgba(255, 255, 255, 0.1)',
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                🚀
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
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
                :style="{
                  position: 'relative',
                  background: 'rgba(255, 255, 255, 0.05)',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  borderRadius: isMobile ? '15px' : '20px',
                  padding: isMobile ? '25px 15px' : '30px 20px',
                  textAlign: 'center',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                  overflow: 'hidden',
                  transform:
                    activeService === idx
                      ? 'translateY(-10px)'
                      : 'translateY(0)',
                  boxShadow:
                    activeService === idx
                      ? '0 15px 40px rgba(102, 126, 234, 0.3)'
                      : 'none',
                }"
              >
                <div
                  :style="{
                    width: responsive.serviceIconBox,
                    height: responsive.serviceIconBox,
                    margin: '0 auto 20px',
                    background:
                      'linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2))',
                    borderRadius: isMobile ? '15px' : '20px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    transition: 'all 0.3s',
                    transform:
                      activeService === idx
                        ? 'scale(1.1) rotate(5deg)'
                        : 'scale(1)',
                  }"
                >
                  <span :style="{ fontSize: responsive.serviceIconSize }">{{
                    service.icon
                  }}</span>
                </div>
                <h4
                  :style="{
                    color: 'white',
                    fontSize: responsive.bodySize,
                    fontWeight: 600,
                    margin: 0,
                  }"
                >
                  {{ service.name }}
                </h4>
                <div
                  :style="{
                    position: 'absolute',
                    inset: 0,
                    background:
                      'linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9))',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    opacity: activeService === idx ? 1 : 0,
                    transition: 'opacity 0.3s',
                  }"
                >
                  <span
                    :style="{
                      color: 'white',
                      fontSize: responsive.bodySize,
                      fontWeight: 700,
                    }"
                    >Explore →</span
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Contact -->
          <div
            :style="{
              background: 'rgba(255, 255, 255, 0.05)',
              backdropFilter: 'blur(20px)',
              border: '1px solid rgba(255, 255, 255, 0.1)',
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                📞
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
                  margin: 0,
                }"
              >
                Get In Touch
              </h2>
            </div>
            <div :style="{ display: 'grid', gap: responsive.gridGap }">
              <a
                v-for="(contact, idx) in contactMethods"
                :key="idx"
                :href="contact.href"
                :target="contact.href.startsWith('http') ? '_blank' : undefined"
                :rel="
                  contact.href.startsWith('http')
                    ? 'noopener noreferrer'
                    : undefined
                "
                :style="{
                  display: 'flex',
                  alignItems: 'center',
                  gap: isMobile ? '15px' : '20px',
                  padding: isMobile ? '20px' : '25px',
                  background: 'rgba(255, 255, 255, 0.05)',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  borderRadius: isMobile ? '15px' : '20px',
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
                    borderRadius: '15px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    flexShrink: 0,
                    background: contact.color,
                  }"
                >
                  <svg
                    :style="{
                      width: responsive.contactIconSize,
                      height: responsive.contactIconSize,
                      fill: 'white',
                    }"
                    viewBox="0 0 24 24"
                  >
                    <path :d="contact.icon" />
                  </svg>
                </div>
                <div :style="{ flex: 1, minWidth: 0 }">
                  <h4
                    :style="{
                      color: 'white',
                      fontSize: responsive.h4Size,
                      fontWeight: 700,
                      margin: '0 0 5px 0',
                      overflow: 'hidden',
                      textOverflow: 'ellipsis',
                      whiteSpace: isMobile ? 'nowrap' : 'normal',
                    }"
                  >
                    {{ contact.label }}
                  </h4>
                  <p
                    :style="{
                      color: 'rgba(255, 255, 255, 0.6)',
                      fontSize: responsive.smallSize,
                      margin: 0,
                      overflow: 'hidden',
                      textOverflow: 'ellipsis',
                      whiteSpace: isMobile ? 'nowrap' : 'normal',
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
                  }"
                >
                  →
                </div>
              </a>
            </div>
          </div>

          <!-- Location -->
          <div
            :style="{
              background: 'rgba(255, 255, 255, 0.05)',
              backdropFilter: 'blur(20px)',
              border: '1px solid rgba(255, 255, 255, 0.1)',
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                📍
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
                  margin: 0,
                }"
              >
                Our Location
              </h2>
            </div>
            <div
              :style="{
                padding: isMobile ? '20px' : '25px',
                background: 'rgba(255, 255, 255, 0.05)',
                borderRadius: '15px',
                border: '1px solid rgba(255, 255, 255, 0.1)',
                marginBottom: isMobile ? '20px' : '25px',
              }"
            >
              <h4
                :style="{
                  color: 'white',
                  fontSize: responsive.h4Size,
                  fontWeight: 700,
                  marginBottom: '15px',
                }"
              >
                {{ address.name }}
              </h4>
              <p
                :style="{
                  color: 'rgba(255, 255, 255, 0.7)',
                  lineHeight: '1.8',
                  fontSize: responsive.bodySize,
                }"
              >
                {{ address.street }}<br />
                {{ address.area }}<br />
                {{ address.cityState }}<br />
                {{ address.country }}
              </p>
            </div>
            <a
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
                background: 'linear-gradient(135deg, #667eea, #764ba2)',
                color: 'white',
                textDecoration: 'none',
                borderRadius: '15px',
                fontWeight: 700,
                fontSize: responsive.bodySize,
                transition: 'all 0.3s',
                overflow: 'hidden',
              }"
              @mouseover="handleMapButtonHoverIn"
              @mouseout="handleMapButtonHoverOut"
            >
              <span :style="{ fontSize: isMobile ? '20px' : '24px' }">🗺️</span>
              Open Maps
            </a>
          </div>

          <!-- Social Media -->
          <div
            :style="{
              background: 'rgba(255, 255, 255, 0.05)',
              backdropFilter: 'blur(20px)',
              border: '1px solid rgba(255, 255, 255, 0.1)',
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                🌐
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
                  margin: 0,
                }"
              >
                Connect With Me
              </h2>
            </div>
            <div
              :style="{
                display: 'grid',
                gridTemplateColumns: isMobile
                  ? 'repeat(2, 1fr)'
                  : 'repeat(auto-fit, minmax(150px, 1fr))',
                gap: '15px',
              }"
            >
              <a
                v-for="(social, idx) in socialLinks"
                :key="idx"
                :href="social.href"
                :style="{
                  position: 'relative',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '10px',
                  padding: isMobile ? '15px 10px' : '18px',
                  background: 'rgba(255, 255, 255, 0.05)',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  borderRadius: '15px',
                  color: 'white',
                  textDecoration: 'none',
                  fontWeight: 600,
                  fontSize: isMobile
                    ? responsive.smallSize
                    : responsive.bodySize,
                  transition: 'all 0.3s',
                  overflow: 'hidden',
                }"
                @mouseover="(e) => handleSocialHoverIn(e, social.color)"
                @mouseout="handleSocialHoverOut"
              >
                <span :style="{ fontSize: isMobile ? '20px' : '24px' }">{{
                  social.emoji
                }}</span>
                {{ social.name }}
              </a>
            </div>
          </div>

          <!-- Team -->
          <div
            :style="{
              background: 'rgba(255, 255, 255, 0.05)',
              backdropFilter: 'blur(20px)',
              border: '1px solid rgba(255, 255, 255, 0.1)',
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
            }"
          >
            <div
              :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #667eea, #764ba2)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                👥
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
                  margin: 0,
                }"
              >
                Meet The Team
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
                v-for="(member, idx) in teamMembers"
                :key="idx"
                @mouseenter="!isMobile && (activeTeam = idx)"
                @mouseleave="!isMobile && (activeTeam = null)"
                @touchstart="activeTeam = idx"
                @touchend="setTimeout(() => (activeTeam = null), 2000)"
                :style="{
                  textAlign: 'center',
                  padding: isMobile ? '25px 15px' : '30px 20px',
                  background: 'rgba(255, 255, 255, 0.05)',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  borderRadius: isMobile ? '15px' : '20px',
                  transition: 'all 0.3s',
                  transform:
                    activeTeam === idx ? 'translateY(-10px)' : 'translateY(0)',
                }"
              >
                <div
                  :style="{
                    position: 'relative',
                    width: isMobile ? '80px' : '100px',
                    height: isMobile ? '80px' : '100px',
                    margin: '0 auto 20px',
                    transition: 'all 0.3s',
                  }"
                >
                  <div
                    :style="{
                      position: 'absolute',
                      inset: '-5px',
                      borderRadius: '50%',
                      background: 'linear-gradient(135deg, #667eea, #764ba2)',
                      opacity: activeTeam === idx ? 1 : 0,
                      transition: 'opacity 0.3s',
                      animation:
                        activeTeam === idx
                          ? 'rotate 2s linear infinite'
                          : 'none',
                    }"
                  />
                  <div
                    :style="{
                      position: 'absolute',
                      inset: 0,
                      background:
                        'linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8))',
                      borderRadius: '50%',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: isMobile ? '28px' : '36px',
                      fontWeight: 800,
                      color: 'white',
                      border: '3px solid rgba(10, 14, 39, 0.5)',
                    }"
                  >
                    {{ member.initials }}
                  </div>
                </div>
                <h4
                  :style="{
                    color: 'white',
                    fontSize: responsive.h4Size,
                    fontWeight: 700,
                    margin: '0 0 8px 0',
                  }"
                >
                  {{ member.name }}
                </h4>
                <p
                  :style="{
                    color: 'rgba(255, 255, 255, 0.6)',
                    fontSize: responsive.smallSize,
                    margin: 0,
                  }"
                >
                  {{ member.role }}
                </p>
              </div>
            </div>
          </div>

          <!-- vCard Button -->
          <button
            @click="saveContact"
            :style="{
              position: 'relative',
              width: '100%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: isMobile ? '10px' : '15px',
              padding: isMobile ? '20px' : '25px',
              background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              border: 'none',
              borderRadius: isMobile ? '15px' : '20px',
              cursor: 'pointer',
              overflow: 'hidden',
              transition: 'all 0.3s',
            }"
            @mouseover="handleVCardHoverIn"
            @mouseout="handleVCardHoverOut"
          >
            <div
              :style="{
                width: isMobile ? '40px' : '50px',
                height: isMobile ? '40px' : '50px',
                background: 'rgba(255, 255, 255, 0.2)',
                borderRadius: '12px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
              }"
            >
              <svg
                :style="{
                  width: isMobile ? '24px' : '28px',
                  height: isMobile ? '24px' : '28px',
                  fill: 'white',
                }"
                viewBox="0 0 24 24"
              >
                <path
                  d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"
                />
              </svg>
            </div>
            <div
              :style="{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'flex-start',
                gap: '3px',
              }"
            >
              <span
                :style="{
                  color: 'white',
                  fontSize: responsive.h4Size,
                  fontWeight: 700,
                }"
                >Save to Contacts</span
              >
              <span
                :style="{
                  color: 'rgba(255, 255, 255, 0.8)',
                  fontSize: responsive.smallSize,
                }"
                >Download vCard</span
              >
            </div>
          </button>
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
        background: 'linear-gradient(135deg, #667eea, #764ba2)',
        border: 'none',
        borderRadius: '50%',
        color: 'white',
        fontSize: isMobile ? '20px' : '24px',
        cursor: 'pointer',
        boxShadow: '0 10px 30px rgba(102, 126, 234, 0.4)',
        transition: 'all 0.3s',
        zIndex: 1000,
        animation: 'fadeIn 0.3s',
      }"
      @mouseover="handleScrollButtonHoverIn"
      @mouseout="handleScrollButtonHoverOut"
    >
      <span :style="{ display: 'block', animation: 'bounce 1s infinite' }"
        >↑</span
      >
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

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
let scrollTimeout = null;

// Responsive breakpoints
const isMobile = computed(() => windowWidth.value < 640);
const isTablet = computed(
  () => windowWidth.value >= 640 && windowWidth.value < 1024
);
const isDesktop = computed(() => windowWidth.value >= 1024);

// Responsive values
const responsive = computed(() => ({
  // Container padding
  containerPadding: isMobile.value ? "15px" : isTablet.value ? "20px" : "20px",

  // Profile image
  profileSize: isMobile.value ? "120px" : isTablet.value ? "150px" : "180px",
  profileBorder: isMobile.value
    ? "3px solid rgba(255, 255, 255, 0.2)"
    : "5px solid rgba(255, 255, 255, 0.2)",

  // Typography
  nameSize: isMobile.value ? "32px" : isTablet.value ? "40px" : "48px",
  h2Size: isMobile.value ? "20px" : isTablet.value ? "24px" : "28px",
  h3Size: isMobile.value ? "24px" : isTablet.value ? "28px" : "32px",
  h4Size: isMobile.value ? "14px" : isTablet.value ? "16px" : "18px",
  bodySize: isMobile.value ? "14px" : "16px",
  smallSize: isMobile.value ? "12px" : "14px",

  // Card padding
  cardPadding: isMobile.value ? "20px" : isTablet.value ? "30px" : "40px",
  cardMarginBottom: isMobile.value ? "20px" : "30px",

  // Quick actions
  actionPadding: isMobile.value ? "15px" : "20px",
  actionIconSize: isMobile.value ? "24px" : "32px",
  actionGap: isMobile.value ? "10px" : "20px",

  // Grid columns
  statsGrid: isMobile.value
    ? "repeat(auto-fit, minmax(100px, 1fr))"
    : "repeat(auto-fit, minmax(150px, 1fr))",
  servicesGrid: isMobile.value
    ? "repeat(auto-fit, minmax(150px, 1fr))"
    : "repeat(auto-fit, minmax(200px, 1fr))",

  // Icon sizes
  serviceIconSize: isMobile.value ? "24px" : "36px",
  serviceIconBox: isMobile.value ? "50px" : "70px",
  contactIconBox: isMobile.value ? "50px" : "60px",
  contactIconSize: isMobile.value ? "24px" : "30px",

  // Borders and radius
  borderRadius: isMobile.value ? "20px" : isTablet.value ? "25px" : "30px",
  cardRadius: isMobile.value ? "15px" : isTablet.value ? "20px" : "25px",

  // Badge padding
  badgePadding: isMobile.value ? "10px 20px" : "15px 30px",

  // Gaps
  flexGap: isMobile.value ? "10px" : "20px",
  gridGap: isMobile.value ? "15px" : "20px",
}));

// Profile Data - Will be loaded from API
const profile = ref({
  name: "",
  qualification: "",
  position: "",
  bio: "",
});

const profileImage = ref(
  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23667eea'/%3E%3Ctext x='75' y='95' font-size='60' fill='white' text-anchor='middle' font-family='Arial' font-weight='bold'%3E?%3C/text%3E%3C/svg%3E"
);

const company = ref({
  logoText: "",
  name: "",
  registrationNo: "",
  department: "",
});

const address = ref({
  name: "",
  street: "",
  area: "",
  cityState: "",
  country: "",
  mapUrl: "",
});

const quickActions = ref([
  { icon: "📞", label: "Call", action: "call" },
  { icon: "✉️", label: "Email", action: "email" },
  { icon: "💬", label: "Chat", action: "whatsapp" },
  { icon: "📅", label: "Book", action: "book" },
]);

const stats = ref([
  { num: "10+", label: "Years Experience" },
  { num: "500+", label: "Projects Done" },
  { num: "98%", label: "Client Satisfaction" },
]);

const services = ref([
  { icon: "🏷️", name: "RFID Technology" },
  { icon: "🖨️", name: "Label Printing" },
  { icon: "💻", name: "Software Development" },
  { icon: "🌐", name: "IoT Implementation" },
  { icon: "🛒", name: "E-commerce Marketing" },
  { icon: "📄", name: "Printing Solutions" },
]);

const contactMethods = ref([
  {
    icon: "M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 0 0-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z",
    label: "Phone",
    subtitle: "",
    href: "tel:",
    color: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
  },
  {
    icon: "M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z",
    label: "Email",
    subtitle: "",
    href: "mailto:",
    color: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)",
  },
  {
    icon: "M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z",
    label: "WhatsApp",
    subtitle: "Chat with me",
    href: "",
    color: "linear-gradient(135deg, #25D366 0%, #128C7E 100%)",
  },
  {
    icon: "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z",
    label: "Website",
    subtitle: "",
    href: "",
    color: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)",
  },
]);

const socialLinks = ref([
  { emoji: "📘", name: "Facebook", href: "#", color: "#1877f2" },
  { emoji: "💼", name: "LinkedIn", href: "#", color: "#0a66c2" },
  { emoji: "📸", name: "Instagram", href: "#", color: "#e4405f" },
  { emoji: "🐦", name: "Twitter", href: "#", color: "#1da1f2" },
]);

const teamMembers = ref([
  { initials: "JD", name: "John Doe", role: "Technical Lead" },
  { initials: "JS", name: "Jane Smith", role: "Project Manager" },
  { initials: "ML", name: "Mike Lee", role: "Designer" },
]);

// Load profile data from API
const loadProfileData = async () => {
  loading.value = true;
  try {
    // Get NFC card ID from route params or query
    const nfcCardId = route.params.id || route.query.nfc_card_id || route.query.id;

    if (!nfcCardId) {
      console.error("No NFC card ID provided");
      profileNotFound.value = true;
      loading.value = false;
      return;
    }

    // Store the current NFC card ID
    currentNfcCardId.value = nfcCardId;

    // Fetch landing page data for this NFC card
    const response = await $api.get(`/nfc-cards/${nfcCardId}/landing-page`);

    if (response.success && response.landing_page) {
      const data = response.landing_page;

      // Basic Info
      profile.value.name = data.name || "Your Name";
      profile.value.qualification = data.qualification || "";
      profile.value.position = data.title || data.position || "Your Position";
      profile.value.bio = data.bio || "Your bio goes here";

      // Profile Image
      if (data.profile_image) {
        profileImage.value = data.profile_image;
      } else if (data.name) {
        // Generate initials from name
        const initials = data.name
          .split(" ")
          .map((n) => n[0])
          .join("")
          .substring(0, 2)
          .toUpperCase();
        profileImage.value = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23667eea'/%3E%3Ctext x='75' y='95' font-size='60' fill='white' text-anchor='middle' font-family='Arial' font-weight='bold'%3E${initials}%3C/text%3E%3C/svg%3E`;
      }

      // Company Info
      company.value.logoText = data.company_logo_text || "CO";
      company.value.name = data.company_name || "Company Name";
      company.value.registrationNo = data.company_registration_no || "";
      company.value.department = data.company_department || "";

      // Address Info
      address.value.name = data.address_name || "";
      address.value.street = data.address_street || "";
      address.value.area = data.address_area || "";
      address.value.cityState = data.address_city_state || "";
      address.value.country = data.address_country || "";
      address.value.mapUrl = data.address_map_url || "";

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
        services.value = data.services;
      }

      // Contact Methods
      if (data.phone_number) {
        contactMethods.value[0].subtitle = data.phone_number;
        contactMethods.value[0].href = `tel:${data.phone_number.replace(
          /\s/g,
          ""
        )}`;
        contactMethods.value[0].label = data.phone_label || "Phone";
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

      // Social Links
      if (
        data.social_links &&
        Array.isArray(data.social_links) &&
        data.social_links.length > 0
      ) {
        socialLinks.value = data.social_links
          .filter((link) => link.url)
          .map((link) => ({
            emoji: link.emoji || "🌐",
            name: link.name || "Social",
            href: link.url,
            color: link.color || "#667eea",
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

      profileNotFound.value = false;
      
      // Check if current user owns this card (to show Edit button)
      await checkCardOwnership();
    } else {
      profileNotFound.value = true;
    }
  } catch (error) {
    console.error("Error loading landing page:", error);
    // Don't show as not found if it's a 404 (card has no landing page yet)
    profileNotFound.value = error.response?.status !== 404;
  } finally {
    loading.value = false;
  }
};

// Functions
const getParticleStyle = (n) => ({
  left: `${Math.random() * 100}%`,
  top: `${Math.random() * 100}%`,
  animationDelay: `${Math.random() * 3}s`,
  animationDuration: `${Math.random() * 3 + 2}s`,
});

const handleAction = (action) => {
  const actions = {
    call: () => (window.location.href = "tel:+60167787616"),
    email: () => (window.location.href = "mailto:chuan.aw@clbgroups.com"),
    whatsapp: () => window.open("https://wa.me/60167787616", "_blank"),
    book: () => alert("Booking feature coming soon!"),
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

const saveContact = () => {
  const vCard = `BEGIN:VCARD
VERSION:3.0
FN:${profile.value.name}
N:${profile.value.name.split(" ").reverse().join(";")};;;
ORG:${company.value.name}
TITLE:${profile.value.position}
TEL;TYPE=CELL:${contactMethods.value[0].subtitle}
EMAIL:${contactMethods.value[1].subtitle}
URL:${contactMethods.value[3].href}
ADR;TYPE=WORK:;;${address.value.street}, ${address.value.area};${
    address.value.cityState
  };;${address.value.country}
NOTE:${profile.value.qualification} - ${company.value.department}
END:VCARD`;

  const blob = new Blob([vCard], { type: "text/vcard" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `${profile.value.name
    .toLowerCase()
    .replace(/\s/g, "-")}-contact.vcf`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
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
  windowWidth.value = window.innerWidth;
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
    e.currentTarget.style.transform = "translateX(10px)";
    e.currentTarget.style.background = "rgba(255, 255, 255, 0.1)";
  }
};

const handleContactHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateX(0)";
    e.currentTarget.style.background = "rgba(255, 255, 255, 0.05)";
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
    e.currentTarget.style.boxShadow = "0 15px 40px rgba(102, 126, 234, 0.5)";
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
    e.currentTarget.style.boxShadow = "0 15px 40px rgba(102, 126, 234, 0.6)";
  }
};

const handleScrollButtonHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(0)";
    e.currentTarget.style.boxShadow = "0 10px 30px rgba(102, 126, 234, 0.4)";
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
    e.currentTarget.style.boxShadow = "0 8px 25px rgba(102, 126, 234, 0.6)";
  }
};

const handleEditButtonHoverOut = (e) => {
  if (!isMobile.value) {
    e.currentTarget.style.transform = "translateY(0)";
    e.currentTarget.style.boxShadow = "0 4px 15px rgba(102, 126, 234, 0.4)";
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
</style>
