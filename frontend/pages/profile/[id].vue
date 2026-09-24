<template>
  <div
    :style="{
      minHeight: '100vh',
      background: designSettings.backgroundColor,
      color: designSettings.textColor,
      position: 'relative',
      overflow: 'hidden',
      fontFamily: designSettings.fontFamily,
    }"
  >
    <!-- Animations -->
    <component :is="'style'">
      {{
        `
        @keyframes float { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(30px, -30px) scale(1.05); } }
        @keyframes floatSlow { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-20px, 20px); } }
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

    <!-- Modern Gradient Background -->
    <div
      :style="{
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        zIndex: 0,
        background: `linear-gradient(135deg, ${designSettings.backgroundColor} 0%, #1a1f3a 50%, #0f1419 100%)`,
        overflow: 'hidden',
      }"
    >
      <!-- Animated Gradient Orbs -->
      <div
        :style="{
          position: 'absolute',
          width: isMobile ? '400px' : '600px',
          height: isMobile ? '400px' : '600px',
          background: 'radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%)',
          borderRadius: '50%',
          filter: 'blur(40px)',
          animation: 'float 25s infinite ease-in-out',
          top: '-200px',
          left: '-200px',
        }"
      />
      <div
        :style="{
          position: 'absolute',
          width: isMobile ? '350px' : '550px',
          height: isMobile ? '350px' : '550px',
          background: 'radial-gradient(circle, rgba(240, 147, 251, 0.12) 0%, transparent 70%)',
          borderRadius: '50%',
          filter: 'blur(40px)',
          animation: 'float 30s infinite ease-in-out',
          bottom: '-150px',
          right: '-150px',
          animationDelay: '10s',
        }"
      />
      <div
        :style="{
          position: 'absolute',
          width: isMobile ? '300px' : '500px',
          height: isMobile ? '300px' : '500px',
          background: 'radial-gradient(circle, rgba(79, 172, 254, 0.1) 0%, transparent 70%)',
          borderRadius: '50%',
          filter: 'blur(40px)',
          animation: 'float 35s infinite ease-in-out',
          top: '40%',
          right: '10%',
          animationDelay: '5s',
        }"
      />
      <!-- Subtle grid overlay -->
      <div
        :style="{
          position: 'absolute',
          inset: 0,
          backgroundImage: 'linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px)',
          backgroundSize: '50px 50px',
          opacity: 0.3,
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

    <!-- Preview Mode Banner - Minimal -->
    <Transition name="fade-slide">
      <div
        v-if="isPreviewMode"
        :class="[
          'fixed top-0 left-0 right-0 z-50 text-center',
          'bg-blue-500 text-white font-medium'
        ]"
        :style="{
          fontSize: '10px',
          padding: '3px 8px',
          boxShadow: '0 1px 2px rgba(0, 0, 0, 0.1)'
        }"
      >
        <span>👁️ Preview</span>
      </div>
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
        <div :style="{ fontSize: '80px', marginBottom: '20px' }">📝</div>
        <h2 :style="{ fontSize: responsive.h2Size, marginBottom: '15px', color: 'white' }">
          No Profile Data Yet
        </h2>
        <p
          :style="{
            fontSize: responsive.bodySize,
            color: 'rgba(255, 255, 255, 0.8)',
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
            background: 'linear-gradient(135deg, #667eea, #764ba2)',
            color: 'white',
            border: 'none',
            borderRadius: '50px',
            fontSize: responsive.bodySize,
            fontWeight: 600,
            cursor: 'pointer',
            boxShadow: '0 10px 30px rgba(102, 126, 234, 0.3)',
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => { e.target.style.transform = 'translateY(-2px)'; e.target.style.boxShadow = '0 15px 40px rgba(102, 126, 234, 0.4)'; }"
          @mouseout="(e) => { e.target.style.transform = 'translateY(0)'; e.target.style.boxShadow = '0 10px 30px rgba(102, 126, 234, 0.3)'; }"
        >
          Set Up Profile Now →
        </button>
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
        <!-- Hero Section - Enhanced -->
        <div
          :style="{
            position: 'relative',
            background: responsive.cardBackground,
            backdropFilter: 'blur(24px)',
            WebkitBackdropFilter: 'blur(24px)',
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

          <!-- NFCGo Watermark (shown when Remove Branding is disabled) -->
          <div
            v-if="!isFeatureEnabled('remove_branding')"
            :style="{
              position: 'absolute',
              top: isMobile ? '12px' : '16px',
              left: isMobile ? '12px' : '16px',
              zIndex: 10,
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              padding: isMobile ? '6px 12px' : '8px 14px',
              background: 'rgba(255, 255, 255, 0.95)',
              borderRadius: '8px',
              boxShadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
            }"
          >
            <div
              :style="{
                width: isMobile ? '20px' : '24px',
                height: isMobile ? '20px' : '24px',
                border: '2px solid #2563eb',
                borderRadius: '5px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
              }"
            >
              <svg :width="isMobile ? '12' : '14'" :height="isMobile ? '12' : '14'" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2"/>
                <circle cx="9" cy="10" r="2"/>
                <path d="M15 8h2"/>
                <path d="M15 12h2"/>
                <path d="M7 16h10"/>
              </svg>
            </div>
            <span
              :style="{
                color: '#2563eb',
                fontSize: isMobile ? '13px' : '15px',
                fontWeight: 700,
                letterSpacing: '0.3px',
              }"
            >
              NFCGo
            </span>
          </div>

          <!-- Hero Content -->
          <div
            :style="{
              padding: isMobile ? '48px 24px' : isTablet ? '64px 40px' : '80px 48px',
              position: 'relative',
              zIndex: 1,
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
              <!-- Pronouns Badge -->
              <div
                v-if="profile.pronouns"
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
                  background: 'linear-gradient(135deg, rgba(79, 172, 254, 0.3), rgba(0, 242, 254, 0.3))',
                  color: '#fff',
                  animation: 'fadeInUp 0.6s ease-out forwards',
                  opacity: 0,
                  animationDelay: '0.5s',
                }"
              >
                <span :style="{ fontSize: isMobile ? '16px' : '18px' }">👤</span>
                {{ profile.pronouns }}
              </div>
            </div>
            
            <!-- Tagline -->
            <p
              v-if="profile.tagline"
              :style="{
                color: 'rgba(255, 255, 255, 0.8)',
                fontSize: responsive.bodySize,
                fontStyle: 'italic',
                marginTop: '20px',
                textAlign: 'center',
                animation: 'fadeInUp 0.6s ease-out forwards',
                opacity: 0,
                animationDelay: '0.6s',
              }"
            >
              "{{ profile.tagline }}"
            </p>
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
        </div>

        <!-- Quick Actions -->
        <div
          v-if="quickActions.length > 0"
          :style="{
            display: 'flex',
            justifyContent: 'center',
            gap: responsive.actionGap,
            marginBottom: responsive.sectionGap,
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

        <!-- Main Content Grid -->
        <div :style="{ display: 'grid', gap: responsive.sectionGap }">
          <!-- Company Card (Combined: Company Info + Video + Team) -->
          <div
            v-if="showCompanySection || showVideoSection || showTeamSection"
            ref="companyCardRef"
            @mousemove="!isMobile && handleCardTilt"
            @mouseleave="!isMobile && resetCardTilt"
            :style="{
              position: 'relative',
              background: responsive.cardBackground,
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
            
            <!-- Company Info Sub-section -->
            <div
              v-if="hasCompanyData"
              :style="{
                marginBottom: (showVideoSection || showTeamSection) ? '30px' : '0',
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
                      borderRadius: isMobile ? '15px' : '20px',
                      border: '2px solid rgba(255, 255, 255, 0.1)',
                    }"
                  />
                  <!-- Fallback to logo text -->
                  <template v-else>
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
                  </template>
                </div>
                
                <!-- Company Info -->
                <div :style="{ flex: 1, minWidth: isMobile ? '100%' : 'auto' }">
                  <h3
                    v-if="company.name && isFieldVisible('companyTeam', 'companyName')"
                    :style="{
                      fontSize: responsive.h3Size,
                      fontWeight: 800,
                      color: 'white',
                      marginBottom: '10px',
                    }"
                  >
                    {{ company.name }}
                  </h3>
                  
                  <!-- Registration No -->
                  <p
                    v-if="company.registrationNo && isFieldVisible('companyTeam', 'companyRegNo')"
                    :style="{
                      color: 'rgba(255, 255, 255, 0.6)',
                      fontSize: responsive.smallSize,
                      marginBottom: '8px',
                    }"
                  >
                    📋 {{ company.registrationNo }}
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
                        background: 'rgba(102, 126, 234, 0.2)',
                        borderRadius: '12px',
                        fontSize: responsive.smallSize,
                        color: 'rgba(255, 255, 255, 0.8)',
                      }"
                    >
                      🏭 {{ company.industry }}
                    </span>
                    <span
                      v-if="company.establishedYear && isFieldVisible('companyTeam', 'establishedYear')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '5px',
                        padding: '4px 12px',
                        background: 'rgba(240, 147, 251, 0.2)',
                        borderRadius: '12px',
                        fontSize: responsive.smallSize,
                        color: 'rgba(255, 255, 255, 0.8)',
                      }"
                    >
                      📅 Est. {{ company.establishedYear }}
                    </span>
                    <span
                      v-if="company.employeeCount && isFieldVisible('companyTeam', 'employeeCount')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '5px',
                        padding: '4px 12px',
                        background: 'rgba(79, 172, 254, 0.2)',
                        borderRadius: '12px',
                        fontSize: responsive.smallSize,
                        color: 'rgba(255, 255, 255, 0.8)',
                      }"
                    >
                      👥 {{ company.employeeCount }} employees
                    </span>
                  </div>
                  
                  <!-- Company Description -->
                  <p
                    v-if="company.description && isFieldVisible('companyTeam', 'companyDescription')"
                    :style="{
                      color: 'rgba(255, 255, 255, 0.7)',
                      fontSize: responsive.bodySize,
                      lineHeight: '1.6',
                      marginBottom: '12px',
                    }"
                    v-html="company.description"
                  />
                  <div
                    v-if="company.department"
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
            
            <!-- Video Sub-section -->
            <div
              v-if="showVideoSection"
              :style="{
                marginBottom: showTeamSection ? '30px' : '0',
                paddingTop: hasCompanyData ? '20px' : '0',
                borderTop: hasCompanyData ? '1px solid rgba(255, 255, 255, 0.1)' : 'none',
              }"
            >
              <div
                v-if="videoData.title"
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
                    background: 'linear-gradient(135deg, #f093fb, #f5576c)',
                    borderRadius: '12px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '16px' : '20px',
                  }"
                >
                  🎥
                </div>
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 700,
                    color: 'white',
                    margin: 0,
                  }"
                >
                  {{ videoData.title }}
                </h3>
              </div>

              <!-- Video Player -->
              <div
                :style="{
                  position: 'relative',
                  paddingBottom: '56.25%', /* 16:9 aspect ratio */
                  height: 0,
                  overflow: 'hidden',
                  borderRadius: '15px',
                  background: 'rgba(0, 0, 0, 0.3)',
                  boxShadow: '0 10px 40px rgba(0, 0, 0, 0.3)',
                  marginBottom: videoData.description ? (isMobile ? '12px' : '15px') : '0',
                }"
              >
                <iframe
                  :src="getEmbedUrl(videoData.url)"
                  :style="{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: '100%',
                    border: 'none',
                    borderRadius: '15px',
                  }"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                />
              </div>

              <!-- Video Description -->
              <p
                v-if="videoData.description"
                :style="{
                  color: 'rgba(255, 255, 255, 0.7)',
                  lineHeight: '1.6',
                  fontSize: responsive.bodySize,
                  margin: 0,
                  textAlign: 'center',
                }"
              >
                {{ videoData.description }}
              </p>
            </div>
            
            <!-- Team Sub-section -->
            <div
              v-if="showTeamSection"
              :style="{
                paddingTop: (hasCompanyData || showVideoSection) ? '20px' : '0',
                borderTop: (hasCompanyData || showVideoSection) ? '1px solid rgba(255, 255, 255, 0.1)' : 'none',
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
                    background: 'linear-gradient(135deg, #667eea, #764ba2)',
                    borderRadius: '12px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '16px' : '20px',
                  }"
                >
                  👥
                </div>
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 700,
                    color: 'white',
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
                        background: 'linear-gradient(135deg, #667eea, #764ba2)',
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
                        border: '2px solid rgba(10, 14, 39, 0.5)',
                      }"
                    />
                    <div
                      v-else
                      :style="{
                        position: 'absolute',
                        inset: 0,
                        background:
                          'linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8))',
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: isMobile ? '20px' : '28px',
                        fontWeight: 800,
                        color: 'white',
                        border: '2px solid rgba(10, 14, 39, 0.5)',
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
                      margin: '0 0 6px 0',
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
                  <!-- View Profile Link Indicator -->
                  <p
                    v-if="member.landing_page_url"
                    :style="{
                      color: 'rgba(102, 126, 234, 0.8)',
                      fontSize: responsive.smallSize,
                      marginTop: '6px',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      gap: '4px',
                    }"
                  >
                    View Profile →
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
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
              border: responsive.cardBorder,
              borderRadius: responsive.cardRadius,
              padding: responsive.cardPadding,
              boxShadow: responsive.cardShadow,
              transition: 'box-shadow 0.3s ease',
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
            
            <!-- About Me Sub-section -->
            <div v-if="hasAboutData" :style="{ marginBottom: (showEducationSection || showAwardsSection) ? '30px' : '0' }">
              <p
                v-if="profile.bio && isFieldVisible('profileAchievements', 'bio')"
                :style="{
                  color: 'rgba(255, 255, 255, 0.8)',
                  lineHeight: '1.8',
                  whiteSpace: 'normal',
                  overflowWrap: 'break-word',
                  fontSize: responsive.bodySize,
                  marginBottom: stats.length > 0 ? (isMobile ? '20px' : '30px') : '0',
                }"
              >
                {{ profile.bio }}
              </p>
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
            
            <!-- Education Sub-section -->
            <div
              v-if="showEducationSection"
              :style="{
                marginBottom: showAwardsSection ? '30px' : '0',
                paddingTop: hasAboutData ? '20px' : '0',
                borderTop: hasAboutData ? '1px solid rgba(255, 255, 255, 0.1)' : 'none',
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
                    background: 'linear-gradient(135deg, #4facfe, #00f2fe)',
                    borderRadius: '12px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '16px' : '20px',
                  }"
                >
                  🎓
                </div>
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 700,
                    color: 'white',
                    margin: 0,
                  }"
                >
                  Education & Certifications
                </h3>
              </div>
              
              <!-- Education List -->
              <div v-if="profile.education?.length > 0 && isFieldVisible('profileAchievements', 'education')" :style="{ marginBottom: profile.certifications?.length > 0 ? '24px' : '0' }">
                <div :style="{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }">
                  <span :style="{ fontSize: '18px' }">📚</span>
                  <h4 :style="{ color: 'rgba(255, 255, 255, 0.9)', fontSize: responsive.h4Size, margin: 0, fontWeight: 600 }">Education</h4>
                </div>
                <div :style="{ display: 'grid', gap: '16px', position: 'relative', paddingLeft: isMobile ? '0' : '20px' }">
                  <!-- Timeline line -->
                  <div v-if="!isMobile" :style="{ position: 'absolute', left: '6px', top: '8px', bottom: '8px', width: '2px', background: 'linear-gradient(180deg, #4facfe, #00f2fe)', borderRadius: '2px' }" />
                  <div
                    v-for="(edu, idx) in profile.education"
                    :key="idx"
                    :style="{
                      position: 'relative',
                      padding: isMobile ? '16px' : '20px',
                      background: 'linear-gradient(135deg, rgba(79, 172, 254, 0.1), rgba(0, 242, 254, 0.05))',
                      borderRadius: '16px',
                      border: '1px solid rgba(79, 172, 254, 0.2)',
                      transition: 'all 0.3s ease',
                    }"
                    @mouseover="(e) => { e.currentTarget.style.transform = 'translateX(5px)'; e.currentTarget.style.borderColor = 'rgba(79, 172, 254, 0.4)'; }"
                    @mouseout="(e) => { e.currentTarget.style.transform = 'translateX(0)'; e.currentTarget.style.borderColor = 'rgba(79, 172, 254, 0.2)'; }"
                  >
                    <!-- Timeline dot -->
                    <div v-if="!isMobile" :style="{ position: 'absolute', left: '-26px', top: '24px', width: '12px', height: '12px', background: 'linear-gradient(135deg, #4facfe, #00f2fe)', borderRadius: '50%', border: '2px solid rgba(10, 14, 39, 0.9)' }" />
                    <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '8px' }">
                      <div :style="{ flex: 1 }">
                        <p :style="{ color: 'white', fontSize: responsive.bodySize, fontWeight: 700, marginBottom: '6px' }">{{ edu.degree || edu.name }}</p>
                        <p v-if="edu.institution" :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: responsive.smallSize, display: 'flex', alignItems: 'center', gap: '6px' }">
                          <span>🏛️</span> {{ edu.institution }}
                        </p>
                      </div>
                      <span v-if="edu.year" :style="{ padding: '4px 12px', background: 'rgba(79, 172, 254, 0.2)', borderRadius: '20px', color: '#7dd3fc', fontSize: responsive.smallSize, fontWeight: 600, whiteSpace: 'nowrap' }">{{ edu.year }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Certifications List -->
              <div v-if="profile.certifications?.length > 0 && isFieldVisible('profileAchievements', 'certifications')">
                <div :style="{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }">
                  <span :style="{ fontSize: '18px' }">📜</span>
                  <h4 :style="{ color: 'rgba(255, 255, 255, 0.9)', fontSize: responsive.h4Size, margin: 0, fontWeight: 600 }">Certifications</h4>
                </div>
                <div :style="{ display: 'grid', gridTemplateColumns: isMobile ? '1fr' : 'repeat(2, 1fr)', gap: '16px' }">
                  <div
                    v-for="(cert, idx) in profile.certifications"
                    :key="idx"
                    :style="{
                      padding: isMobile ? '16px' : '20px',
                      background: 'linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05))',
                      borderRadius: '16px',
                      border: '1px solid rgba(102, 126, 234, 0.2)',
                      transition: 'all 0.3s ease',
                      display: 'flex',
                      gap: '12px',
                      alignItems: 'flex-start',
                    }"
                    @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-3px)'; e.currentTarget.style.boxShadow = '0 10px 30px rgba(102, 126, 234, 0.2)'; }"
                    @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; }"
                  >
                    <div :style="{ width: '40px', height: '40px', background: 'linear-gradient(135deg, #667eea, #764ba2)', borderRadius: '12px', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }">
                      <span :style="{ fontSize: '20px' }">✅</span>
                    </div>
                    <div :style="{ flex: 1 }">
                      <p :style="{ color: 'white', fontSize: responsive.bodySize, fontWeight: 700, marginBottom: '4px' }">{{ cert.name }}</p>
                      <p v-if="cert.issuer" :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: responsive.smallSize, marginBottom: '4px' }">{{ cert.issuer }}</p>
                      <span v-if="cert.year" :style="{ color: 'rgba(255, 255, 255, 0.5)', fontSize: responsive.smallSize }">{{ cert.year }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Awards Sub-section -->
            <div
              v-if="showAwardsSection"
              :style="{
                paddingTop: (hasAboutData || showEducationSection) ? '20px' : '0',
                borderTop: (hasAboutData || showEducationSection) ? '1px solid rgba(255, 255, 255, 0.1)' : 'none',
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
                    background: 'linear-gradient(135deg, #ffd700, #ffed4e)',
                    borderRadius: '12px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: isMobile ? '16px' : '20px',
                  }"
                >
                  🏆
                </div>
                <h3
                  :style="{
                    fontSize: responsive.h3Size,
                    fontWeight: 700,
                    color: 'white',
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
                    background: 'linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 237, 78, 0.05))',
                    borderRadius: '20px',
                    border: '1px solid rgba(255, 215, 0, 0.2)',
                    transition: 'all 0.3s ease',
                    overflow: 'hidden',
                  }"
                  @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-5px)'; e.currentTarget.style.boxShadow = '0 15px 40px rgba(255, 215, 0, 0.15)'; e.currentTarget.style.borderColor = 'rgba(255, 215, 0, 0.4)'; }"
                  @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; e.currentTarget.style.borderColor = 'rgba(255, 215, 0, 0.2)'; }"
                >
                  <!-- Trophy decoration -->
                  <div :style="{ position: 'absolute', top: '-10px', right: '-10px', fontSize: '60px', opacity: 0.1, transform: 'rotate(15deg)' }">🏆</div>
                  <div :style="{ position: 'relative', zIndex: 1 }">
                    <div :style="{ display: 'flex', alignItems: 'flex-start', gap: '14px' }">
                      <div :style="{ width: '50px', height: '50px', background: 'linear-gradient(135deg, #ffd700, #ffed4e)', borderRadius: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: '0 4px 15px rgba(255, 215, 0, 0.3)' }">
                        <span :style="{ fontSize: '24px' }">🏆</span>
                      </div>
                      <div :style="{ flex: 1 }">
                        <p :style="{ color: '#ffd700', fontSize: responsive.bodySize, fontWeight: 700, marginBottom: '6px', textShadow: '0 0 20px rgba(255, 215, 0, 0.3)' }">{{ award.title || award.name }}</p>
                        <p v-if="award.organization" :style="{ color: 'rgba(255, 255, 255, 0.8)', fontSize: responsive.smallSize, marginBottom: '4px', display: 'flex', alignItems: 'center', gap: '6px' }">
                          <span>🏛️</span> {{ award.organization }}
                        </p>
                        <span v-if="award.year" :style="{ display: 'inline-block', marginTop: '8px', padding: '4px 12px', background: 'rgba(255, 215, 0, 0.2)', borderRadius: '20px', color: '#ffd700', fontSize: responsive.smallSize, fontWeight: 600 }">{{ award.year }}</span>
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
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
                @click="openServiceModal(service)"
                :style="{
                  position: 'relative',
                  background: 'rgba(255, 255, 255, 0.05)',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  borderRadius: isMobile ? '15px' : '20px',
                  padding: isMobile ? '25px 15px' : '30px 20px',
                  textAlign: 'center',
                  cursor: 'pointer',
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                  overflow: 'hidden',
                  transform:
                    activeService === idx
                      ? 'translateY(-8px) scale(1.03)'
                      : 'translateY(0) scale(1)',
                  boxShadow:
                    activeService === idx
                      ? '0 20px 50px rgba(102, 126, 234, 0.4)'
                      : '0 4px 15px rgba(0, 0, 0, 0.1)',
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
            
            <!-- Service Details (Features, Price, etc.) -->
            <div
              v-if="serviceDetails.features.length > 0 || serviceDetails.price || serviceDetails.tags.length > 0 || serviceDetails.bookingEnabled"
              :style="{
                marginTop: responsive.gridGap,
                padding: responsive.cardPadding,
                background: 'rgba(102, 126, 234, 0.1)',
                borderRadius: responsive.cardRadius,
                border: '1px solid rgba(102, 126, 234, 0.2)',
              }"
            >
              <h3
                :style="{
                  fontSize: responsive.h3Size,
                  fontWeight: 700,
                  color: 'white',
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
                  <span :style="{ fontSize: responsive.h2Size, fontWeight: 800, color: '#00ff88' }">
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
                    color: 'rgba(255, 255, 255, 0.8)',
                    fontSize: responsive.bodySize,
                  }"
                >
                  ⏱️ {{ serviceDetails.duration }}
                </div>
              </div>
              
              <!-- Features -->
              <div v-if="serviceDetails.features.length > 0 && isFieldVisible('services', 'serviceFeatures')" :style="{ marginBottom: '20px' }">
                <h4 :style="{ fontSize: responsive.h4Size, color: 'white', marginBottom: '12px' }">Features:</h4>
                <ul :style="{ listStyle: 'none', padding: 0, margin: 0 }">
                  <li
                    v-for="(feature, idx) in serviceDetails.features"
                    :key="idx"
                    :style="{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '10px',
                      padding: '8px 0',
                      color: 'rgba(255, 255, 255, 0.8)',
                      fontSize: responsive.bodySize,
                    }"
                  >
                    <span :style="{ color: '#00ff88' }">✓</span> {{ typeof feature === 'object' ? (feature.feature || feature.name || feature) : feature }}
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
                      background: 'rgba(102, 126, 234, 0.3)',
                      border: '1px solid rgba(102, 126, 234, 0.5)',
                      borderRadius: '16px',
                      fontSize: responsive.smallSize,
                      color: '#a8b3ff',
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
                    background: 'rgba(255, 255, 255, 0.1)',
                    border: '1px solid rgba(255, 255, 255, 0.2)',
                    borderRadius: '25px',
                    color: 'white',
                    fontSize: responsive.bodySize,
                    fontWeight: 600,
                    textDecoration: 'none',
                    transition: 'all 0.3s',
                  }"
                  @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.2)'"
                  @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
                >
                  📄 Download Brochure
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
                    background: 'linear-gradient(135deg, #667eea, #764ba2)',
                    borderRadius: '25px',
                    color: 'white',
                    fontSize: responsive.bodySize,
                    fontWeight: 600,
                    textDecoration: 'none',
                    transition: 'all 0.3s',
                    boxShadow: '0 4px 15px rgba(102, 126, 234, 0.3)',
                  }"
                  @mouseover="(e) => e.target.style.transform = 'translateY(-2px)'"
                  @mouseout="(e) => e.target.style.transform = 'translateY(0)'"
                >
                  📅 Book Now
                </a>
              </div>
            </div>
          </div>

          <!-- Expertise & Skills -->
          <div
            v-if="expertise.length > 0"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #f093fb, #f5576c)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                💡
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
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
                  padding: isMobile ? '15px' : '20px',
                  background: 'rgba(255, 255, 255, 0.05)',
                  borderRadius: '15px',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                }"
              >
                <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }">
                  <span :style="{ color: 'white', fontSize: responsive.bodySize, fontWeight: 600 }">{{ skill.name }}</span>
                  <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: responsive.smallSize }">{{ skill.level }}%</span>
                </div>
                <div :style="{ width: '100%', height: '8px', background: 'rgba(255, 255, 255, 0.1)', borderRadius: '4px', overflow: 'hidden' }">
                  <div
                    :style="{
                      width: `${skill.level || 0}%`,
                      height: '100%',
                      background: 'linear-gradient(90deg, #667eea, #764ba2)',
                      borderRadius: '4px',
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
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
            v-if="showLocationSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
                v-if="address.name && isFieldVisible('location', 'addressName')"
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
                {{ address.cityState }}<span v-if="address.postalCode"> {{ address.postalCode }}</span><br />
                {{ address.country }}
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
            v-if="showSocialSection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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

          <!-- Working Hours -->
          <div
            v-if="workingHours.length > 0"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #f093fb, #f5576c)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                🕒
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
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
                <span :style="{ color: 'white', fontSize: responsive.bodySize, fontWeight: 600 }">{{ hour.day }}</span>
                <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: responsive.bodySize }">{{ hour.hours || 'Closed' }}</span>
              </div>
            </div>
          </div>

          <!-- Gallery -->
          <div
            v-if="showGallerySection"
            :style="{
              background: responsive.cardBackground,
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
                🖼️
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
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
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
                💼
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
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
                  background: 'linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.05))',
                  borderRadius: '24px',
                  border: '1px solid rgba(102, 126, 234, 0.15)',
                  overflow: 'hidden',
                  transition: 'all 0.4s ease',
                  cursor: 'pointer',
                }"
                @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-8px)'; e.currentTarget.style.boxShadow = '0 20px 50px rgba(102, 126, 234, 0.2)'; e.currentTarget.style.borderColor = 'rgba(102, 126, 234, 0.3)'; }"
                @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; e.currentTarget.style.borderColor = 'rgba(102, 126, 234, 0.15)'; }"
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
                  <div :style="{ position: 'absolute', inset: 0, background: 'linear-gradient(to top, rgba(10, 14, 39, 0.9) 0%, transparent 60%)' }" />
                  <!-- Project number badge -->
                  <div :style="{ position: 'absolute', top: '12px', left: '12px', width: '36px', height: '36px', background: 'linear-gradient(135deg, #667eea, #764ba2)', borderRadius: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontWeight: 700, fontSize: '14px' }">
                    {{ String(idx + 1).padStart(2, '0') }}
                  </div>
                </div>
                
                <!-- Content -->
                <div :style="{ padding: isMobile ? '20px' : '24px' }">
                  <h3 :style="{ color: 'white', fontSize: responsive.h3Size, fontWeight: 700, marginBottom: '12px', lineHeight: 1.3 }">{{ project.title }}</h3>
                  
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
                        background: 'rgba(255, 255, 255, 0.08)',
                        borderRadius: '20px',
                        fontSize: responsive.smallSize,
                        color: 'rgba(255, 255, 255, 0.8)',
                      }"
                    >
                      👤 {{ project.client_name }}
                    </span>
                    <span
                      v-if="project.date_completed && isFieldVisible('portfolio', 'dateCompleted')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '6px',
                        padding: '6px 12px',
                        background: 'rgba(255, 255, 255, 0.08)',
                        borderRadius: '20px',
                        fontSize: responsive.smallSize,
                        color: 'rgba(255, 255, 255, 0.8)',
                      }"
                    >
                      📅 {{ project.date_completed }}
                    </span>
                    <span
                      v-if="project.location && isFieldVisible('portfolio', 'location')"
                      :style="{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '6px',
                        padding: '6px 12px',
                        background: 'rgba(255, 255, 255, 0.08)',
                        borderRadius: '20px',
                        fontSize: responsive.smallSize,
                        color: 'rgba(255, 255, 255, 0.8)',
                      }"
                    >
                      📍 {{ project.location }}
                    </span>
                  </div>
                  
                  <p v-if="project.description" :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: responsive.bodySize, lineHeight: '1.7', marginBottom: '16px', display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden' }" v-html="project.description" />
                
                <!-- Skills Used -->
                <div
                  v-if="project.skills_used && project.skills_used.length > 0 && isFieldVisible('portfolio', 'skillsUsed')"
                  :style="{ marginBottom: '15px' }"
>
                  <p :style="{ color: 'rgba(255, 255, 255, 0.5)', fontSize: responsive.smallSize, marginBottom: '8px' }">Skills:</p>
                  <div :style="{ display: 'flex', gap: '8px', flexWrap: 'wrap' }">
                    <span
                      v-for="(skill, skillIdx) in project.skills_used"
                      :key="skillIdx"
                      :style="{
                        padding: '4px 10px',
                        background: 'rgba(79, 172, 254, 0.2)',
                        border: '1px solid rgba(79, 172, 254, 0.3)',
                        borderRadius: '12px',
                        color: '#7dd3fc',
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
                      background: 'rgba(102, 126, 234, 0.2)',
                      border: '1px solid rgba(102, 126, 234, 0.4)',
                      borderRadius: '50px',
                      color: '#a8b3ff',
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
                        background: 'linear-gradient(135deg, #667eea, #764ba2)',
                        borderRadius: '25px',
                        border: 'none',
                        color: 'white',
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        cursor: 'pointer',
                        transition: 'all 0.3s',
                        boxShadow: '0 4px 15px rgba(102, 126, 234, 0.3)',
                      }"
                      @mouseover="(e) => { e.target.style.transform = 'translateY(-2px)'; e.target.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.4)'; }"
                      @mouseout="(e) => { e.target.style.transform = 'translateY(0)'; e.target.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.3)'; }"
                    >
                      🔗 View Project
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
                        background: 'rgba(255, 255, 255, 0.08)',
                        border: '1px solid rgba(255, 255, 255, 0.2)',
                        borderRadius: '25px',
                        color: 'white',
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        textDecoration: 'none',
                        transition: 'all 0.3s',
                      }"
                      @mouseover="(e) => { e.target.style.background = 'rgba(255, 255, 255, 0.15)'; e.target.style.borderColor = 'rgba(255, 255, 255, 0.3)'; }"
                      @mouseout="(e) => { e.target.style.background = 'rgba(255, 255, 255, 0.08)'; e.target.style.borderColor = 'rgba(255, 255, 255, 0.2)'; }"
                    >
                      📥 Download PDF
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
              backdropFilter: 'blur(24px)',
              WebkitBackdropFilter: 'blur(24px)',
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
                gap: '15px',
                marginBottom: isMobile ? '20px' : '30px',
              }"
            >
              <div
                :style="{
                  width: isMobile ? '40px' : '50px',
                  height: isMobile ? '40px' : '50px',
                  background: 'linear-gradient(135deg, #f093fb, #f5576c)',
                  borderRadius: '15px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: isMobile ? '20px' : '24px',
                }"
              >
                📝
              </div>
              <h2
                :style="{
                  fontSize: responsive.h2Size,
                  fontWeight: 700,
                  color: 'white',
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
                  background: 'linear-gradient(135deg, rgba(240, 147, 251, 0.08), rgba(245, 87, 108, 0.05))',
                  borderRadius: '24px',
                  border: '1px solid rgba(240, 147, 251, 0.15)',
                  overflow: 'hidden',
                  transition: 'all 0.4s ease',
                }"
                @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-8px)'; e.currentTarget.style.boxShadow = '0 20px 50px rgba(240, 147, 251, 0.15)'; e.currentTarget.style.borderColor = 'rgba(240, 147, 251, 0.3)'; }"
                @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = 'none'; e.currentTarget.style.borderColor = 'rgba(240, 147, 251, 0.15)'; }"
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
                  <div :style="{ position: 'absolute', inset: 0, background: 'linear-gradient(to top, rgba(10, 14, 39, 0.95) 0%, transparent 70%)' }" />
                  <!-- Category badge on image -->
                  <span v-if="post.category && isFieldVisible('blog', 'blogCategory')" :style="{ position: 'absolute', top: '12px', left: '12px', padding: '6px 14px', background: 'linear-gradient(135deg, #f093fb, #f5576c)', borderRadius: '20px', color: 'white', fontSize: responsive.smallSize, fontWeight: 600 }">{{ post.category }}</span>
                </div>
                
                <!-- Content -->
                <div :style="{ padding: isMobile ? '20px' : '24px' }">
                  <!-- Blog Meta -->
                  <div :style="{ display: 'flex', gap: '12px', marginBottom: '12px', flexWrap: 'wrap', alignItems: 'center' }">
                    <span v-if="post.published_date" :style="{ display: 'flex', alignItems: 'center', gap: '6px', color: 'rgba(255, 255, 255, 0.6)', fontSize: responsive.smallSize }">📅 {{ post.published_date }}</span>
                    <span v-if="post.reading_time && isFieldVisible('blog', 'readingTime')" :style="{ display: 'flex', alignItems: 'center', gap: '6px', padding: '4px 10px', background: 'rgba(255, 255, 255, 0.1)', borderRadius: '15px', color: 'rgba(255, 255, 255, 0.7)', fontSize: responsive.smallSize }">⏱️ {{ post.reading_time }}</span>
                  </div>
                  
                  <h3 :style="{ color: 'white', fontSize: responsive.h3Size, fontWeight: 700, marginBottom: '12px', lineHeight: 1.3, whiteSpace: 'normal', overflowWrap: 'break-word' }">{{ post.title }}</h3>
                  
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
                    <div :style="{ width: '32px', height: '32px', background: 'linear-gradient(135deg, #f093fb, #f5576c)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center' }">
                      <span :style="{ fontSize: '14px' }">✍️</span>
                    </div>
                    <span :style="{ color: 'rgba(255, 255, 255, 0.8)', fontSize: responsive.smallSize, fontWeight: 500 }">{{ post.author_name }}</span>
                  </div>
                  
                  <p v-if="post.excerpt || post.description" :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: responsive.bodySize, lineHeight: '1.7', marginBottom: '16px', display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden' }" v-html="post.excerpt || post.description" />
                  
                  <!-- Tags -->
                  <div v-if="post.tags && post.tags.length > 0 && isFieldVisible('blog', 'blogTags')" :style="{ display: 'flex', gap: '8px', flexWrap: 'wrap', marginBottom: '16px' }">
                    <span
                      v-for="(tag, tagIdx) in post.tags"
                      :key="tagIdx"
                      :style="{
                        padding: '5px 12px',
                        background: 'rgba(240, 147, 251, 0.15)',
                        border: '1px solid rgba(240, 147, 251, 0.25)',
                        borderRadius: '15px',
                        color: '#f0a3fb',
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
                        background: 'linear-gradient(135deg, #f093fb, #f5576c)',
                        border: 'none',
                        borderRadius: '25px',
                        color: 'white',
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        cursor: 'pointer',
                        transition: 'all 0.3s',
                        boxShadow: '0 4px 15px rgba(240, 147, 251, 0.3)',
                      }"
                      @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.boxShadow = '0 6px 20px rgba(240, 147, 251, 0.4)'; }"
                      @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = '0 4px 15px rgba(240, 147, 251, 0.3)'; }"
                    >
                      📖 Read More
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
                        background: 'rgba(255, 255, 255, 0.1)',
                        border: '1px solid rgba(255, 255, 255, 0.2)',
                        borderRadius: '25px',
                        color: 'white',
                        fontSize: responsive.smallSize,
                        fontWeight: 600,
                        textDecoration: 'none',
                        transition: 'all 0.3s',
                      }"
                      @mouseover="(e) => { e.target.style.background = 'rgba(255, 255, 255, 0.15)'; }"
                      @mouseout="(e) => { e.target.style.background = 'rgba(255, 255, 255, 0.1)'; }"
                    >
                      🔗 Visit Link
                    </a>
                  </div>
                </div>
              </article>
            </div>
          </div>

          <!-- Action Buttons Section -->
          <!-- Hide entire Quick Actions section when admin has not assigned any features -->
          <div v-if="availableFeatures && availableFeatures.length > 0" :style="{ 
            background: 'rgba(255, 255, 255, 0.03)',
            borderRadius: '20px',
            padding: isMobile ? '16px' : '20px',
            border: '1px solid rgba(255, 255, 255, 0.08)',
          }">
            <!-- Section Title -->
            <div :style="{ 
              display: 'flex', 
              alignItems: 'center', 
              gap: '8px', 
              marginBottom: '16px',
              paddingBottom: '12px',
              borderBottom: '1px solid rgba(255, 255, 255, 0.1)',
            }">
              <span :style="{ fontSize: '18px' }">⚡</span>
              <span :style="{ color: 'rgba(255, 255, 255, 0.9)', fontSize: '14px', fontWeight: 600, letterSpacing: '0.5px' }">Quick Actions</span>
            </div>
            
            <!-- Buttons Grid -->
            <div :style="{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '12px' }">
              
              <!-- vCard Button -->
              <button
                v-if="isFeatureEnabled('vcard_download')"
                @click="saveContact"
                :style="{
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '8px',
                  padding: '16px 12px',
                  background: 'rgba(102, 126, 234, 0.15)',
                  border: '1px solid rgba(102, 126, 234, 0.3)',
                  borderRadius: '16px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = 'rgba(102, 126, 234, 0.25)'; e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = 'rgba(102, 126, 234, 0.5)'; }"
                @mouseout="(e) => { e.currentTarget.style.background = 'rgba(102, 126, 234, 0.15)'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = 'rgba(102, 126, 234, 0.3)'; }"
              >
                <div :style="{ width: '44px', height: '44px', background: 'linear-gradient(135deg, #667eea, #764ba2)', borderRadius: '12px', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 15px rgba(102, 126, 234, 0.4)' }">
                  <span :style="{ fontSize: '20px' }">📥</span>
                </div>
                <span :style="{ color: 'white', fontSize: '13px', fontWeight: 600 }">Save Contact</span>
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
                  gap: '8px',
                  padding: '16px 12px',
                  background: 'rgba(240, 147, 251, 0.15)',
                  border: '1px solid rgba(240, 147, 251, 0.3)',
                  borderRadius: '16px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = 'rgba(240, 147, 251, 0.25)'; e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = 'rgba(240, 147, 251, 0.5)'; }"
                @mouseout="(e) => { e.currentTarget.style.background = 'rgba(240, 147, 251, 0.15)'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = 'rgba(240, 147, 251, 0.3)'; }"
              >
                <div :style="{ width: '44px', height: '44px', background: 'linear-gradient(135deg, #f093fb, #f5576c)', borderRadius: '12px', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 15px rgba(240, 147, 251, 0.4)' }">
                  <span :style="{ fontSize: '20px' }">✉️</span>
                </div>
                <span :style="{ color: 'white', fontSize: '13px', fontWeight: 600 }">Message</span>
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
                  gap: '8px',
                  padding: '16px 12px',
                  background: 'rgba(79, 172, 254, 0.15)',
                  border: '1px solid rgba(79, 172, 254, 0.3)',
                  borderRadius: '16px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = 'rgba(79, 172, 254, 0.25)'; e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = 'rgba(79, 172, 254, 0.5)'; }"
                @mouseout="(e) => { e.currentTarget.style.background = 'rgba(79, 172, 254, 0.15)'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = 'rgba(79, 172, 254, 0.3)'; }"
              >
                <div :style="{ width: '44px', height: '44px', background: 'linear-gradient(135deg, #4facfe, #00f2fe)', borderRadius: '12px', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 15px rgba(79, 172, 254, 0.4)' }">
                  <span :style="{ fontSize: '20px' }">📱</span>
                </div>
                <span :style="{ color: 'white', fontSize: '13px', fontWeight: 600 }">QR Code</span>
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
                  gap: '8px',
                  padding: '16px 12px',
                  background: 'rgba(56, 239, 125, 0.15)',
                  border: '1px solid rgba(56, 239, 125, 0.3)',
                  borderRadius: '16px',
                  cursor: 'pointer',
                  transition: 'all 0.3s',
                }"
                @mouseover="(e) => { e.currentTarget.style.background = 'rgba(56, 239, 125, 0.25)'; e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = 'rgba(56, 239, 125, 0.5)'; }"
                @mouseout="(e) => { e.currentTarget.style.background = 'rgba(56, 239, 125, 0.15)'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.borderColor = 'rgba(56, 239, 125, 0.3)'; }"
              >
                <div :style="{ width: '44px', height: '44px', background: 'linear-gradient(135deg, #11998e, #38ef7d)', borderRadius: '12px', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 15px rgba(56, 239, 125, 0.4)' }">
                  <span :style="{ fontSize: '20px' }">🔗</span>
                </div>
                <span :style="{ color: 'white', fontSize: '13px', fontWeight: 600 }">Share</span>
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
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                border: 'none',
                borderRadius: '14px',
                cursor: 'pointer',
                transition: 'all 0.3s',
                textDecoration: 'none',
                boxShadow: '0 4px 20px rgba(102, 126, 234, 0.3)',
              }"
              @mouseover="(e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.boxShadow = '0 8px 30px rgba(102, 126, 234, 0.5)'; }"
              @mouseout="(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = '0 4px 20px rgba(102, 126, 234, 0.3)'; }"
            >
              <span :style="{ fontSize: '18px' }">📅</span>
              <span :style="{ color: 'white', fontSize: '14px', fontWeight: 600 }">Book Appointment</span>
            </a>

          </div>
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
          background: 'linear-gradient(135deg, rgba(30, 30, 60, 0.95), rgba(20, 20, 40, 0.98))',
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
            color: 'white',
            fontSize: '18px',
          }"
        >✕</button>

        <!-- Header -->
        <div :style="{ marginBottom: '24px', textAlign: 'center' }">
          <div :style="{ fontSize: '40px', marginBottom: '12px' }">✉️</div>
          <h3 :style="{ color: 'white', fontSize: '24px', fontWeight: 700, marginBottom: '8px' }">Send a Message</h3>
          <p :style="{ color: 'rgba(255, 255, 255, 0.6)', fontSize: '14px' }">I'll get back to you as soon as possible</p>
        </div>

        <!-- Success Message -->
        <div
          v-if="contactFormSuccess"
          :style="{
            padding: '20px',
            background: 'rgba(34, 197, 94, 0.2)',
            border: '1px solid rgba(34, 197, 94, 0.4)',
            borderRadius: '16px',
            textAlign: 'center',
            marginBottom: '16px',
          }"
        >
          <div :style="{ fontSize: '40px', marginBottom: '8px' }">✅</div>
          <p :style="{ color: '#22c55e', fontWeight: 600 }">Message sent successfully!</p>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="submitContactForm" :style="{ display: 'grid', gap: '16px' }">
          <div>
            <label :style="{ display: 'block', color: 'rgba(255, 255, 255, 0.8)', fontSize: '14px', marginBottom: '6px' }">Your Name</label>
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
                color: 'white',
                fontSize: '15px',
                outline: 'none',
              }"
              placeholder="John Doe"
            />
          </div>
          <div>
            <label :style="{ display: 'block', color: 'rgba(255, 255, 255, 0.8)', fontSize: '14px', marginBottom: '6px' }">Email Address</label>
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
                color: 'white',
                fontSize: '15px',
                outline: 'none',
              }"
              placeholder="john@example.com"
            />
          </div>
          <div>
            <label :style="{ display: 'block', color: 'rgba(255, 255, 255, 0.8)', fontSize: '14px', marginBottom: '6px' }">Phone (Optional)</label>
            <input
              v-model="contactForm.phone"
              type="tel"
              :style="{
                width: '100%',
                padding: '14px 16px',
                background: 'rgba(255, 255, 255, 0.08)',
                border: '1px solid rgba(255, 255, 255, 0.15)',
                borderRadius: '12px',
                color: 'white',
                fontSize: '15px',
                outline: 'none',
              }"
              placeholder="+60 12 345 6789"
            />
          </div>
          <div>
            <label :style="{ display: 'block', color: 'rgba(255, 255, 255, 0.8)', fontSize: '14px', marginBottom: '6px' }">Message</label>
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
                color: 'white',
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
              background: contactFormSubmitting ? 'rgba(255, 255, 255, 0.1)' : 'linear-gradient(135deg, #f093fb, #f5576c)',
              border: 'none',
              borderRadius: '12px',
              color: 'white',
              fontSize: '16px',
              fontWeight: 600,
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
          background: 'linear-gradient(135deg, rgba(30, 30, 60, 0.95), rgba(20, 20, 40, 0.98))',
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
            color: 'white',
            fontSize: '18px',
          }"
        >✕</button>

        <!-- Header -->
        <div :style="{ marginBottom: '24px' }">
          <div :style="{ fontSize: '40px', marginBottom: '12px' }">📱</div>
          <h3 :style="{ color: 'white', fontSize: '24px', fontWeight: 700, marginBottom: '8px' }">Scan to Connect</h3>
          <p :style="{ color: 'rgba(255, 255, 255, 0.6)', fontSize: '14px' }">Share this profile instantly</p>
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
          <p :style="{ color: 'white', fontSize: '18px', fontWeight: 600 }">{{ profile.name }}</p>
          <p :style="{ color: 'rgba(255, 255, 255, 0.6)', fontSize: '14px' }">{{ profile.position }}</p>
        </div>

        <!-- Copy Link Button -->
        <button
          @click="copyProfileLink"
          :style="{
            width: '100%',
            padding: '14px',
            background: 'linear-gradient(135deg, #4facfe, #00f2fe)',
            border: 'none',
            borderRadius: '12px',
            color: 'white',
            fontSize: '15px',
            fontWeight: 600,
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '8px',
          }"
        >
          <span>📋</span> Copy Profile Link
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
          background: 'linear-gradient(135deg, rgba(30, 30, 60, 0.95), rgba(20, 20, 40, 0.98))',
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
            color: 'white',
            fontSize: '18px',
          }"
        >✕</button>

        <!-- Header -->
        <div :style="{ marginBottom: '24px' }">
          <div :style="{ fontSize: '40px', marginBottom: '12px' }">🔗</div>
          <h3 :style="{ color: 'white', fontSize: '24px', fontWeight: 700, marginBottom: '8px' }">Share Profile</h3>
          <p :style="{ color: 'rgba(255, 255, 255, 0.6)', fontSize: '14px' }">Share on your favorite platform</p>
        </div>

        <!-- Social Buttons Grid -->
        <div :style="{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '16px', marginBottom: '20px' }">
          <!-- Facebook -->
          <a
            :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#1877f2', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">📘</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">Facebook</span>
          </a>
          <!-- Twitter/X -->
          <a
            :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(profile.name + ' - ' + profile.position)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#000000', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">🐦</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">X</span>
          </a>
          <!-- LinkedIn -->
          <a
            :href="`https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(profile.name)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#0a66c2', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">💼</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">LinkedIn</span>
          </a>
          <!-- WhatsApp -->
          <a
            :href="`https://wa.me/?text=${encodeURIComponent(profile.name + ' - ' + currentUrl)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#25d366', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">💬</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">WhatsApp</span>
          </a>
          <!-- Telegram -->
          <a
            :href="`https://t.me/share/url?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(profile.name)}`"
            target="_blank"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#0088cc', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">✈️</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">Telegram</span>
          </a>
          <!-- Email -->
          <a
            :href="`mailto:?subject=${encodeURIComponent('Check out ' + profile.name)}&body=${encodeURIComponent('Here is ' + profile.name + '\'s profile: ' + currentUrl)}`"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#ea4335', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">📧</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">Email</span>
          </a>
          <!-- SMS -->
          <a
            :href="`sms:?body=${encodeURIComponent(profile.name + ' - ' + currentUrl)}`"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', textDecoration: 'none' }"
          >
            <div :style="{ width: '56px', height: '56px', background: '#34c759', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">💬</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">SMS</span>
          </a>
          <!-- Copy Link -->
          <button
            @click="copyProfileLink"
            :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', background: 'none', border: 'none', cursor: 'pointer' }"
          >
            <div :style="{ width: '56px', height: '56px', background: 'linear-gradient(135deg, #667eea, #764ba2)', borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }">📋</div>
            <span :style="{ color: 'rgba(255, 255, 255, 0.7)', fontSize: '12px' }">Copy</span>
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
          background: 'linear-gradient(135deg, rgba(30, 30, 60, 0.98), rgba(20, 20, 40, 0.99))',
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
            color: 'white',
            fontSize: '20px',
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.2)'"
          @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
        >✕</button>

        <!-- Service Icon & Title -->
        <div :style="{ textAlign: 'center', marginBottom: '24px' }">
          <div :style="{ 
            width: '80px', 
            height: '80px', 
            margin: '0 auto 16px', 
            background: 'linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3))', 
            borderRadius: '24px', 
            display: 'flex', 
            alignItems: 'center', 
            justifyContent: 'center',
            fontSize: '40px',
          }">
            {{ selectedService.icon || '🚀' }}
          </div>
          <h3 :style="{ color: 'white', fontSize: '28px', fontWeight: 700, marginBottom: '8px' }">
            {{ selectedService.name }}
          </h3>
          <div v-if="selectedService.category" :style="{ display: 'flex', justifyContent: 'center', gap: '12px', flexWrap: 'wrap' }">
            <span :style="{ 
              padding: '6px 16px', 
              background: 'rgba(102, 126, 234, 0.2)', 
              border: '1px solid rgba(102, 126, 234, 0.4)',
              borderRadius: '20px', 
              color: '#a8b3ff', 
              fontSize: '13px',
              fontWeight: 500,
            }">
              {{ selectedService.category }}
            </span>
            <span v-if="selectedService.duration" :style="{ 
              padding: '6px 16px', 
              background: 'rgba(240, 147, 251, 0.15)', 
              border: '1px solid rgba(240, 147, 251, 0.4)',
              borderRadius: '20px', 
              color: '#f0a8ff', 
              fontSize: '13px',
              fontWeight: 500,
            }">
              ⏱️ {{ selectedService.duration }}
            </span>
          </div>
        </div>

        <!-- Price Section -->
        <div v-if="selectedService.price" :style="{ 
          textAlign: 'center', 
          marginBottom: '24px',
          padding: '20px',
          background: 'rgba(0, 255, 136, 0.1)',
          borderRadius: '16px',
          border: '1px solid rgba(0, 255, 136, 0.2)',
        }">
          <div :style="{ display: 'flex', alignItems: 'baseline', justifyContent: 'center', gap: '12px' }">
            <span :style="{ fontSize: '32px', fontWeight: 800, color: '#00ff88' }">
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
          <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            📝 Description
          </h4>
          <p :style="{ 
            color: 'rgba(255, 255, 255, 0.75)', 
            fontSize: '15px', 
            lineHeight: '1.7',
            padding: '16px',
            background: 'rgba(255, 255, 255, 0.05)',
            borderRadius: '12px',
          }" v-html="selectedService.description"></p>
        </div>

        <!-- Features -->
        <div v-if="selectedService.features && selectedService.features.length > 0" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            ✨ Key Features
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
              <span :style="{ color: '#00ff88', fontSize: '16px' }">✓</span>
              <span :style="{ color: 'rgba(255, 255, 255, 0.85)', fontSize: '14px' }">
                {{ typeof feature === 'object' ? (feature.feature || feature.name || feature) : feature }}
              </span>
            </div>
          </div>
        </div>

        <!-- Tags -->
        <div v-if="selectedService.tags && selectedService.tags.length > 0" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            🏷️ Keywords
          </h4>
          <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '8px' }">
            <span 
              v-for="(tag, idx) in selectedService.tags" 
              :key="idx"
              :style="{
                padding: '6px 14px',
                background: 'rgba(102, 126, 234, 0.2)',
                border: '1px solid rgba(102, 126, 234, 0.3)',
                borderRadius: '16px',
                color: '#a8b3ff',
                fontSize: '13px',
              }"
            >
              #{{ typeof tag === 'object' ? (tag.tag || tag.name || tag) : tag }}
            </span>
          </div>
        </div>

        <!-- Gallery -->
        <div v-if="selectedService.gallery && selectedService.gallery.length > 0" :style="{ marginBottom: '24px' }">
          <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            🖼️ Gallery
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
          <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
            🎬 Promo Video
          </h4>
          <a 
            :href="selectedService.video" 
            target="_blank"
            :style="{
              display: 'flex',
              alignItems: 'center',
              gap: '12px',
              padding: '16px',
              background: 'rgba(255, 0, 0, 0.1)',
              border: '1px solid rgba(255, 0, 0, 0.2)',
              borderRadius: '12px',
              textDecoration: 'none',
              transition: 'all 0.3s',
            }"
          >
            <span :style="{ fontSize: '24px' }">▶️</span>
            <span :style="{ color: 'white', fontSize: '14px', fontWeight: 500 }">Watch Video</span>
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
              background: 'rgba(255, 255, 255, 0.1)',
              border: '1px solid rgba(255, 255, 255, 0.2)',
              borderRadius: '14px',
              color: 'white',
              fontSize: '15px',
              fontWeight: 600,
              textDecoration: 'none',
              transition: 'all 0.3s',
            }"
            @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.15)'"
            @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
          >
            📄 Download Brochure
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
              background: 'linear-gradient(135deg, #667eea, #764ba2)',
              borderRadius: '14px',
              color: 'white',
              fontSize: '15px',
              fontWeight: 600,
              textDecoration: 'none',
              transition: 'all 0.3s',
              boxShadow: '0 8px 25px rgba(102, 126, 234, 0.4)',
            }"
            @mouseover="(e) => e.target.style.transform = 'translateY(-2px)'"
            @mouseout="(e) => e.target.style.transform = 'translateY(0)'"
          >
            📅 Book Now
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
          background: 'linear-gradient(135deg, rgba(30, 30, 60, 0.98), rgba(20, 20, 40, 0.99))',
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
            color: 'white',
            fontSize: '20px',
            zIndex: 10,
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.2)'"
          @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
        >✕</button>

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
          <div :style="{ position: 'absolute', inset: 0, background: 'linear-gradient(to top, rgba(20, 20, 40, 1) 0%, transparent 60%)' }" />
        </div>

        <!-- Content -->
        <div :style="{ padding: isMobile ? '24px' : '32px', marginTop: selectedProject.cover_image ? '-60px' : 0, position: 'relative', zIndex: 2 }">
          <!-- Title -->
          <h3 :style="{ color: 'white', fontSize: '28px', fontWeight: 700, marginBottom: '16px' }">
            {{ selectedProject.title }}
          </h3>

          <!-- Meta Info -->
          <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap', marginBottom: '20px' }">
            <span v-if="selectedProject.category" :style="{ padding: '6px 16px', background: 'rgba(102, 126, 234, 0.2)', border: '1px solid rgba(102, 126, 234, 0.4)', borderRadius: '20px', color: '#a8b3ff', fontSize: '13px' }">
              {{ selectedProject.category }}
            </span>
            <span v-if="selectedProject.client_name" :style="{ padding: '6px 16px', background: 'rgba(255, 255, 255, 0.08)', borderRadius: '20px', color: 'rgba(255, 255, 255, 0.8)', fontSize: '13px' }">
              👤 {{ selectedProject.client_name }}
            </span>
            <span v-if="selectedProject.date_completed" :style="{ padding: '6px 16px', background: 'rgba(255, 255, 255, 0.08)', borderRadius: '20px', color: 'rgba(255, 255, 255, 0.8)', fontSize: '13px' }">
              📅 {{ selectedProject.date_completed }}
            </span>
            <span v-if="selectedProject.location" :style="{ padding: '6px 16px', background: 'rgba(255, 255, 255, 0.08)', borderRadius: '20px', color: 'rgba(255, 255, 255, 0.8)', fontSize: '13px' }">
              📍 {{ selectedProject.location }}
            </span>
          </div>

          <!-- Description -->
          <div v-if="selectedProject.description" :style="{ marginBottom: '24px' }">
            <p :style="{ color: 'rgba(255, 255, 255, 0.75)', fontSize: '15px', lineHeight: '1.8' }" v-html="selectedProject.description"></p>
          </div>

          <!-- Gallery -->
          <div v-if="selectedProject.gallery && selectedProject.gallery.length > 0" :style="{ marginBottom: '24px' }">
            <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }">
              🖼️ Project Gallery
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
            <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px' }">
              🏷️ Skills & Tags
            </h4>
            <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '8px' }">
              <span 
                v-for="(skill, idx) in (selectedProject.skills_used || [])" 
                :key="'skill-' + idx"
                :style="{ padding: '6px 14px', background: 'rgba(79, 172, 254, 0.2)', border: '1px solid rgba(79, 172, 254, 0.3)', borderRadius: '16px', color: '#7dd3fc', fontSize: '13px' }"
              >{{ typeof skill === 'object' ? (skill.skill || skill.name) : skill }}</span>
              <span 
                v-for="(tag, idx) in (selectedProject.tags || [])" 
                :key="'tag-' + idx"
                :style="{ padding: '6px 14px', background: 'rgba(102, 126, 234, 0.2)', border: '1px solid rgba(102, 126, 234, 0.3)', borderRadius: '16px', color: '#a8b3ff', fontSize: '13px' }"
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
                background: 'linear-gradient(135deg, #667eea, #764ba2)',
                borderRadius: '14px',
                color: 'white',
                fontSize: '15px',
                fontWeight: 600,
                textDecoration: 'none',
                transition: 'all 0.3s',
                boxShadow: '0 8px 25px rgba(102, 126, 234, 0.4)',
              }"
            >
              🔗 View Project
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
                background: 'rgba(255, 255, 255, 0.1)',
                border: '1px solid rgba(255, 255, 255, 0.2)',
                borderRadius: '14px',
                color: 'white',
                fontSize: '15px',
                fontWeight: 600,
                textDecoration: 'none',
              }"
            >
              📄 Download PDF
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
          background: 'linear-gradient(135deg, rgba(30, 30, 60, 0.98), rgba(20, 20, 40, 0.99))',
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
            color: 'white',
            fontSize: '20px',
            zIndex: 10,
            transition: 'all 0.3s',
          }"
          @mouseover="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.2)'"
          @mouseout="(e) => e.target.style.background = 'rgba(255, 255, 255, 0.1)'"
        >✕</button>

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
             <div :style="{ position: 'absolute', bottom: 0, left: 0, right: 0, height: '60px', background: 'linear-gradient(to top, rgba(20, 20, 40, 1) 0%, transparent 100%)', pointerEvents: 'none' }" />
        </div>

        <!-- Content -->
        <div :style="{ padding: isMobile ? '24px' : '32px', position: 'relative', zIndex: 2 }">
          <!-- Meta Info Header -->
           <div :style="{ display: 'flex', gap: '12px', flexWrap: 'wrap', marginBottom: '16px', alignItems: 'center' }">
             <span v-if="selectedBlog.category" :style="{ padding: '6px 14px', background: 'rgba(240, 147, 251, 0.15)', border: '1px solid rgba(240, 147, 251, 0.3)', borderRadius: '20px', color: '#f0a3fb', fontSize: '13px', fontWeight: 600 }">
              {{ selectedBlog.category }}
             </span>
             <span v-if="selectedBlog.published_date" :style="{ color: 'rgba(255, 255, 255, 0.6)', fontSize: '14px', display: 'flex', alignItems: 'center', gap: '6px' }">
               📅 {{ selectedBlog.published_date }}
             </span>
             <span v-if="selectedBlog.reading_time" :style="{ color: 'rgba(255, 255, 255, 0.6)', fontSize: '14px', display: 'flex', alignItems: 'center', gap: '6px' }">
               ⏱️ {{ selectedBlog.reading_time }}
             </span>
           </div>

          <!-- Title -->
          <h3 :style="{ color: 'white', fontSize: isMobile ? '24px' : '32px', fontWeight: 700, marginBottom: '20px', lineHeight: 1.3 }">
            {{ selectedBlog.title }}
          </h3>

          <!-- Author -->
          <div v-if="selectedBlog.author_name" :style="{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '24px', paddingBottom: '24px', borderBottom: '1px solid rgba(255, 255, 255, 0.1)' }">
              <div :style="{ width: '40px', height: '40px', background: 'linear-gradient(135deg, #f093fb, #f5576c)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px' }">
                  ✍️
              </div>
              <div>
                  <div :style="{ color: 'white', fontWeight: 600, fontSize: '15px' }">{{ selectedBlog.author_name }}</div>
                  <div :style="{ color: 'rgba(255, 255, 255, 0.5)', fontSize: '13px' }">Author</div>
              </div>
          </div>

          <!-- Full Content (Rich Text) -->
          <div
            v-if="selectedBlog.content || selectedBlog.description"
            :style="{
                color: 'rgba(255, 255, 255, 0.85)',
                fontSize: responsive.bodySize,
                lineHeight: '1.8',
                marginBottom: '32px',
                whiteSpace: 'pre-wrap' // Preserve newlines if not HTML, but v-html handles HTML
            }"
            class="rich-text-content"
            v-html="selectedBlog.content || selectedBlog.description"
          />

          <!-- Tags -->
          <div v-if="selectedBlog.tags && selectedBlog.tags.length > 0" :style="{ marginBottom: '32px' }">
            <h4 :style="{ color: 'white', fontSize: '16px', fontWeight: 600, marginBottom: '12px' }">
              🏷️ Tags
            </h4>
            <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '8px' }">
              <span
                v-for="(tag, idx) in selectedBlog.tags"
                :key="'tag-' + idx"
                :style="{ padding: '6px 14px', background: 'rgba(255, 255, 255, 0.05)', border: '1px solid rgba(255, 255, 255, 0.1)', borderRadius: '16px', color: 'rgba(255, 255, 255, 0.7)', fontSize: '13px' }"
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
                  background: 'linear-gradient(135deg, #f093fb, #f5576c)',
                  borderRadius: '30px',
                  color: 'white',
                  fontSize: '16px',
                  fontWeight: 600,
                  textDecoration: 'none',
                  boxShadow: '0 8px 25px rgba(240, 147, 251, 0.4)',
                  transition: 'all 0.3s'
                }"
                @mouseover="(e) => e.target.style.transform = 'translateY(-2px)'"
                @mouseout="(e) => e.target.style.transform = 'translateY(0)'"
              >
                🔗 Visit External Page
              </a>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";

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

// Responsive breakpoints
const isMobile = computed(() => windowWidth.value < 640);
const isTablet = computed(
  () => windowWidth.value >= 640 && windowWidth.value < 1024
);
const isDesktop = computed(() => windowWidth.value >= 1024);

// Responsive values - Modern design system
const responsive = computed(() => ({
  // Container
  containerPadding: isMobile.value ? "20px 0" : isTablet.value ? "30px" : "40px",
  maxWidth: "1200px",

  // Profile image
  profileSize: isMobile.value ? "140px" : isTablet.value ? "160px" : "200px",
  profileBorder: "4px solid rgba(255, 255, 255, 0.15)",

  // Typography - Modern scale
  nameSize: isMobile.value ? "36px" : isTablet.value ? "48px" : "56px",
  h2Size: isMobile.value ? "24px" : isTablet.value ? "28px" : "32px",
  h3Size: isMobile.value ? "20px" : isTablet.value ? "24px" : "28px",
  h4Size: isMobile.value ? "16px" : isTablet.value ? "18px" : "20px",
  bodySize: isMobile.value ? "15px" : "16px",
  smallSize: isMobile.value ? "13px" : "14px",

  // Card styling - Glassmorphism
  cardPadding: isMobile.value ? "16px" : isTablet.value ? "32px" : "40px",
  cardMarginBottom: isMobile.value ? "24px" : "32px",
  cardBackground: "rgba(255, 255, 255, 0.03)",
  cardBorder: "1px solid rgba(255, 255, 255, 0.08)",
  cardShadow: "0 8px 32px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05)",
  cardHoverShadow: "0 12px 48px rgba(102, 126, 234, 0.15)",

  // Quick actions
  actionPadding: isMobile.value ? "16px" : "20px",
  actionIconSize: isMobile.value ? "28px" : "36px",
  actionGap: isMobile.value ? "12px" : "16px",

  // Grid columns
  statsGrid: isMobile.value
    ? "repeat(auto-fit, minmax(110px, 1fr))"
    : "repeat(auto-fit, minmax(160px, 1fr))",
  servicesGrid: isMobile.value
    ? "repeat(auto-fit, minmax(160px, 1fr))"
    : "repeat(auto-fit, minmax(220px, 1fr))",
  galleryGrid: isMobile.value ? "repeat(2, 1fr)" : "repeat(3, 1fr)",

  // Icon sizes
  serviceIconSize: isMobile.value ? "28px" : "40px",
  serviceIconBox: isMobile.value ? "56px" : "72px",
  contactIconBox: isMobile.value ? "56px" : "64px",
  contactIconSize: isMobile.value ? "28px" : "32px",

  // Borders and radius - Softer edges
  borderRadius: isMobile.value ? "24px" : isTablet.value ? "28px" : "32px",
  cardRadius: isMobile.value ? "0" : isTablet.value ? "24px" : "28px",
  smallRadius: isMobile.value ? "12px" : "16px",

  // Badge styling
  badgePadding: isMobile.value ? "10px 20px" : "12px 24px",

  // Gaps - More breathing room
  flexGap: isMobile.value ? "12px" : "20px",
  gridGap: isMobile.value ? "16px" : "24px",
  sectionGap: isMobile.value ? "24px" : "32px",
}));

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
  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23667eea'/%3E%3Ctext x='75' y='95' font-size='60' fill='white' text-anchor='middle' font-family='Arial' font-weight='bold'%3E?%3C/text%3E%3C/svg%3E"
);

// Added: Design config and visible fields control
const visibleFieldsConfig = ref([]);
const buttonClasses = ref('');
const currentThemeConfig = ref(null);

// Design variables - for dynamic styling
const designSettings = reactive({
  backgroundColor: '#0a0e27',
  textColor: '#ffffff',
  fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif',
  theme: 'default',
  buttonStyle: 'solid',
});

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
  'video',      // 4. Video - company/personal intro video
  'services',   // 5. Services - what you offer
  'portfolio',  // 6. Portfolio - work samples
  'blog',       // 7. Blog - thought leadership
  'contact',    // 8. Contact - how to reach
  'location',   // 9. Location - where to find
  'social',     // 10. Social - connect online
  'team',       // 11. Team - who else is involved
  'gallery',    // 12. Gallery - visual showcase
  'education',  // 13. Education - credentials
  'awards',     // 14. Awards - achievements
  'vcard',      // 15. vCard - always last, save contact action
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

const hasCompanyData = computed(() => {
  return !!(company.value.name || company.value.department || company.value.logo || company.value.logoText);
});

const hasAboutData = computed(() => {
  return !!(profile.value.bio || stats.value.length > 0);
});

const hasVideoData = computed(() => {
  return !!videoData.value.url;
});

const hasServicesData = computed(() => {
  return services.value.length > 0;
});

const hasContactData = computed(() => {
  return contactMethods.value.some(c => c.subtitle);
});

const hasLocationData = computed(() => {
  return !!(address.value.name || address.value.street || address.value.cityState);
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
const showVideoSection = computed(() => hasVideoData.value && isSubSectionVisible('companyTeam', 'video'));
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
});

// ==================== C. QUICK ACTIONS (dynamically generated) ====================
// Quick Actions - dynamically generated from contactMethods (respects field visibility)
const quickActions = computed(() => {
  const actions = [];
  
  // Phone (check field visibility)
  if (contactMethods.value[0]?.subtitle && isFieldVisible('contact', 'phone')) {
    actions.push({ icon: "📞", label: "Call", action: "call" });
  }
  
  // Email
  if (contactMethods.value[1]?.subtitle) {
    actions.push({ icon: "✉️", label: "Email", action: "email" });
  }
  
  // WhatsApp (check field visibility)
  if (contactMethods.value[2]?.subtitle && contactMethods.value[2]?.href && isFieldVisible('contact', 'whatsapp')) {
    actions.push({ icon: "💬", label: "Chat", action: "whatsapp" });
  }
  
  // Appointment (check booking_integration feature)
  if (appointmentLink.value && isFeatureEnabled('booking_integration')) {
    actions.push({ icon: "📅", label: "Book", action: "appointment" });
  }
  
  // Payment
  if (paymentButton.value.url) {
    actions.push({ icon: "💳", label: paymentButton.value.text || "Pay", action: "payment" });
  }
  
  // Website
  if (contactMethods.value[3]?.subtitle) {
    actions.push({ icon: "🌐", label: "Website", action: "website" });
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

const videoData = ref({
  url: '',
  title: '',
  description: '',
});

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
  {
    icon: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z",
    label: "Address",
    subtitle: "",
    href: "",
    color: "linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)",
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

// Check if a feature is enabled
// A feature is enabled only if:
// 1. Admin has assigned it (in availableFeatures)
// 2. User has enabled it (not explicitly set to false)
const isFeatureEnabled = (featureKey) => {
  // If availableFeatures array exists but is empty, it means admin didn't assign any features
  // In this case, nothing should be enabled
  if (availableFeatures.value.length === 0) {
    console.log(`🚫 Feature "${featureKey}" disabled: No features assigned by admin`);
    return false;
  }
  
  // Check if admin has assigned this feature
  const isAssigned = availableFeatures.value.includes(featureKey);
  
  // Check if user has enabled it
  const isEnabled = features.value[featureKey] !== false;
  
  // Both conditions must be met
  const result = isAssigned && isEnabled;
  
  // Debug logging
  if (typeof window !== 'undefined') {
    console.log(`🔍 isFeatureEnabled("${featureKey}"): assigned=${isAssigned}, enabled=${isEnabled}, result=${result}`);
  }
  return result;
};

// Load preview data from localStorage (matches BusinessProfileBuilder.vue profileData structure)
const loadPreviewData = (data) => {
  console.log('📋 Loading preview data:', data);
  
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

  // Profile Image
  if (data.image) {
    profileImage.value = data.image;
  } else if (data.name) {
    const initials = data.name.split(" ").map((n) => n[0]).join("").substring(0, 2).toUpperCase();
    profileImage.value = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23667eea'/%3E%3Ctext x='75' y='95' font-size='60' fill='white' text-anchor='middle' font-family='Arial' font-weight='bold'%3E${initials}%3C/text%3E%3C/svg%3E`;
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
  address.value.name = data.addressName || "";
  address.value.street = data.addressStreet || "";
  address.value.area = data.addressArea || "";
  address.value.cityState = data.addressCityState || "";
  address.value.country = data.addressCountry || "";
  address.value.postalCode = data.postalCode || "";
  address.value.mapUrl = data.addressMapUrl || "";

  // ============ VIDEO SECTION ============
  if (data.companyVideo) {
    videoData.value.url = data.companyVideo;
    videoData.value.title = data.companyVideoTitle || '';
    videoData.value.description = data.companyVideoDescription || '';
  } else {
    videoData.value.url = '';
    videoData.value.title = '';
    videoData.value.description = '';
  }

  // ============ CONTACT METHODS ============
  contactMethods.value[0].subtitle = data.phoneNumber || data.contactNumber || data.phone || "";
  contactMethods.value[0].href = (data.phoneNumber || data.contactNumber) ? `tel:${(data.phoneNumber || data.contactNumber).replace(/\s/g, '')}` : "";
  contactMethods.value[1].subtitle = data.emailAddress || data.email || "";
  contactMethods.value[1].href = (data.emailAddress || data.email) ? `mailto:${data.emailAddress || data.email}` : "";
  contactMethods.value[2].subtitle = data.whatsappNumber || data.whatsapp ? "Chat with me" : "";
  contactMethods.value[2].href = (data.whatsappNumber || data.whatsapp) ? `https://wa.me/${(data.whatsappNumber || data.whatsapp).replace(/[^0-9]/g, '')}` : "";
  contactMethods.value[3].subtitle = data.websiteUrl || data.website || "";
  contactMethods.value[3].href = data.websiteUrl || data.website || "";
  
  // Contact Address for "Get In Touch" (uses profile contact address, NOT company location)
  const contactAddressPreview = data.address || '';
  if (contactAddressPreview) {
    contactMethods.value[4].subtitle = contactAddressPreview;
    contactMethods.value[4].href = `https://maps.google.com/?q=${encodeURIComponent(contactAddressPreview)}`;
    contactMethods.value[4].label = "Contact Address";
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
        icon: service.icon || "🚀",
        category: service.category || service.service_category || service.serviceCategory || "",
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
        emoji: getPlatformEmoji(link.platform),
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
  if (data.font) {
    const fontMap = {
      'inter': 'Inter, sans-serif',
      'roboto': 'Roboto, sans-serif',
      'playfair': 'Playfair Display, serif',
      'poppins': 'Poppins, sans-serif',
      'montserrat': 'Montserrat, sans-serif',
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
    console.log('📐 Section layout loaded from preview:', sectionLayout.value);
  } else {
    console.log('⚠️ No sectionLayout in preview data, using defaults');
  }
  
  // ============ FIELD LAYOUT (with sub-section states) ============
  if (data.fieldLayout && typeof data.fieldLayout === 'object') {
    fieldLayout.value = data.fieldLayout;
    console.log('📐 Field layout loaded from preview:', fieldLayout.value);
  }

  // ============ FEATURE SETTINGS ============
  // Load available features (assigned by admin)
  if (data.availableFeatures && Array.isArray(data.availableFeatures)) {
    availableFeatures.value = data.availableFeatures;
    console.log('🔐 Available features (assigned by admin):', availableFeatures.value);
  } else {
    // If no availableFeatures provided, set to empty array (meaning admin didn't assign any features)
    availableFeatures.value = [];
    console.log('🚫 No features assigned by admin');
  }
  
  console.log('📥 Raw features from preview data:', data.features);
  if (data.features && typeof data.features === 'object' && Object.keys(data.features).length > 0) {
    // Merge features - preview data overrides defaults
    Object.keys(data.features).forEach(key => {
      features.value[key] = data.features[key];
      console.log(`  → Setting feature "${key}" to:`, data.features[key]);
    });
    console.log('✨ Features after merge:', JSON.stringify(features.value));
  } else {
    console.log('⚠️ No features in preview data, using defaults:', features.value);
  }
  if (data.featureOrder && Array.isArray(data.featureOrder)) {
    featureOrder.value = data.featureOrder;
    console.log('✨ Feature order loaded from preview:', featureOrder.value);
  }

  profileNotFound.value = false;
  console.log('✅ Preview data loaded successfully');
};

// Helper functions for platform data
const getPlatformEmoji = (platform) => {
  const platformMap = {
    instagram: "📸",
    facebook: "📘",
    tiktok: "🎵",
    youtube: "📺",
    spotify: "🎵",
    whatsapp: "💬",
    x: "🐦",
    snapchat: "👻",
    linkedin: "💼",
    telegram: "✈️",
    website: "🌐",
    email: "📧",
    // New platforms
    reddit: "🤖",
    pinterest: "📌",
    wechat: "💬",
    douyin: "🎵",
    discord: "🎮",
    threads: "🧵",
    xiaohongshu: "📕",
    quora: "❓",
    custom: "🔗",
  };
  return platformMap[platform?.toLowerCase()] || "🔗";
};

const getPlatformColor = (platform) => {
  const colorMap = {
    instagram: "#e4405f",
    facebook: "#1877f2",
    tiktok: "#000000",
    youtube: "#ff0000",
    spotify: "#1db954",
    whatsapp: "#25d366",
    x: "#000000",
    snapchat: "#fffc00",
    linkedin: "#0a66c2",
    telegram: "#0088cc",
    website: "#667eea",
    email: "#ea4335",
    // New platforms
    reddit: "#ff4500",
    pinterest: "#e60023",
    wechat: "#07c160",
    douyin: "#000000",
    discord: "#5865f2",
    threads: "#000000",
    xiaohongshu: "#fe2c55",
    quora: "#b92b27",
    custom: "#667eea",
  };
  return colorMap[platform?.toLowerCase()] || "#667eea";
};

// Helper function to convert video URLs to embed format
const getEmbedUrl = (url) => {
  if (!url) return '';
  
  // YouTube
  const youtubeRegex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/;
  const youtubeMatch = url.match(youtubeRegex);
  if (youtubeMatch) {
    return `https://www.youtube.com/embed/${youtubeMatch[1]}?rel=0`;
  }
  
  // Vimeo
  const vimeoRegex = /(?:vimeo\.com\/)([0-9]+)/;
  const vimeoMatch = url.match(vimeoRegex);
  if (vimeoMatch) {
    return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
  }
  
  // If already an embed URL or direct link, return as is
  return url;
};

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
            console.log('📋 Loading preview data from localStorage');
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
    console.log(`🔍 Fetching landing page for NFC card: ${nfcCardId}`);
    console.log(`🌐 Request URL: /nfc-cards/${nfcCardId}/landing-page`);
    const response = await $api.get(`/nfc-cards/${nfcCardId}/landing-page`);
    console.log('📡 API Response:', response);
    console.log('📄 Landing Page Data:', response.landing_page);
    console.log('✅ Has data?', !!response.landing_page);

    if (response.success && response.landing_page) {
      const data = response.landing_page;
      console.log('✅ Loading landing page data...');

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

      // Address Info
      address.value.name = data.address_name || "";
      address.value.street = data.address_street || data.address || "";
      address.value.area = data.address_area || "";
      address.value.cityState = data.address_city_state || "";
      address.value.country = data.address_country || "";
      address.value.postalCode = data.postal_code || "";
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
          icon: service.icon || "🚀",
          category: service.category || service.service_category || service.serviceCategory || "",
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

      // Contact Address - use profile contact address for "Get In Touch" section
      // This is separate from location address (Company & Team → Location & Address)
      const contactAddress = data.address || '';
      
      if (contactAddress) {
        contactMethods.value[4].subtitle = contactAddress;
        contactMethods.value[4].href = `https://maps.google.com/?q=${encodeURIComponent(contactAddress)}`;
        contactMethods.value[4].label = "Contact Address";
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
            emoji: getPlatformEmoji(link.platform),
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

      // Video Data
      if (data.company_video) {
        videoData.value.url = data.company_video;
        videoData.value.title = data.video_title || '';
        videoData.value.description = data.video_description || '';
      } else {
        videoData.value.url = '';
        videoData.value.title = '';
        videoData.value.description = '';
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
        console.log('📐 Section layout loaded:', sectionLayout.value);
      }
      
      // ==================== B. LOAD FIELD LAYOUT (with sub-section states) ====================
      if (data.field_layout && typeof data.field_layout === 'object') {
        fieldLayout.value = data.field_layout;
        console.log('📐 Field layout loaded:', fieldLayout.value);
      }

      // ==================== C. LOAD FEATURE SETTINGS ====================
      // Load available features (assigned by admin)
      if (data.available_features && Array.isArray(data.available_features)) {
        availableFeatures.value = data.available_features;
        console.log('🔐 Available features (from API):', availableFeatures.value);
      } else {
        // If no available_features from API, set to empty array (meaning admin didn't assign any features)
        availableFeatures.value = [];
        console.log('🚫 No features assigned by admin (API)');
      }
      
      if (data.features && typeof data.features === 'object') {
        features.value = { ...features.value, ...data.features };
        console.log('✨ Features loaded:', features.value);
      }
      if (data.feature_order && Array.isArray(data.feature_order)) {
        featureOrder.value = data.feature_order;
        console.log('✨ Feature order loaded:', featureOrder.value);
      }

      // === Apply design_config (complete design object) ===
      if (data.design_config) {
        console.log('🎨 Applying design config:', data.design_config);
        applyDesignConfig(data.design_config);
      }
      
      // === Apply direct design fields (backward compatibility) ===
      applyDirectDesignFields(data);

      // === Use visible_fields to control display ===
      if (data.visible_fields) {
        console.log('👁️ Visible fields config:', data.visible_fields);
        visibleFieldsConfig.value = data.visible_fields;
      }

      profileNotFound.value = false;
      noProfileData.value = false;
      
      // Check if current user owns this card (to show Edit button)
      await checkCardOwnership();
    } else {
      console.warn('⚠️ No landing page data returned');
      noProfileData.value = true;
    }
  } catch (error) {
    // 404 = Card exists but has no landing page data yet
    if (error.response?.status === 404) {
      console.log('ℹ️ No landing page data found for this card (expected for new cards)');
      noProfileData.value = true;
      // Check if user owns this card to show setup button
      await checkCardOwnership();
    } else {
      // Other errors = Card doesn't exist or server error
      console.error("Error loading landing page:", error);
      profileNotFound.value = true;
    }
  } finally {
    loading.value = false;
  }
};

// Added: Function to apply design configuration
const applyDesignConfig = (designConfig) => {
  try {
    console.log('🎨 Applying design config:', designConfig);
    
    // Apply Theme
    if (designConfig.theme) {
      const theme = designConfig.theme;
      designSettings.theme = theme.id || 'default';
      if (theme.backgroundColor) {
        designSettings.backgroundColor = theme.backgroundColor;
      }
    }

    // Apply Font
    if (designConfig.font) {
      const font = designConfig.font;
      if (font.family) {
        designSettings.fontFamily = font.family;
      }
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
    
    console.log('✅ Design settings applied:', designSettings);
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
  if (data.font) {
    // Map font ID to font family
    const fontMap = {
      'inter': 'Inter, sans-serif',
      'roboto': 'Roboto, sans-serif',
      'playfair': 'Playfair Display, serif',
      'poppins': 'Poppins, sans-serif',
      'montserrat': 'Montserrat, sans-serif',
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

// Functions
const getParticleStyle = (n) => ({
  left: `${Math.random() * 100}%`,
  top: `${Math.random() * 100}%`,
  animationDelay: `${Math.random() * 3}s`,
  animationDuration: `${Math.random() * 3 + 2}s`,
});

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

// Current page URL for QR Code
const currentUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.href;
  }
  return '';
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

// Submit contact form
const submitContactForm = async () => {
  try {
    contactFormSubmitting.value = true;
    
    // Get the NFC card ID from route
    const cardId = route.params.id;
    
    // Send the contact form data to backend
    const response = await $api(`/contact-form`, {
      method: 'POST',
      body: {
        nfc_card_id: cardId,
        name: contactForm.value.name,
        email: contactForm.value.email,
        phone: contactForm.value.phone,
        message: contactForm.value.message,
        profile_name: profile.value.name,
        profile_email: profile.value.email,
      },
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
    alert('Failed to send message. Please try again.');
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
