<template>
  <div class="auth-page">
    <!-- LEFT PANEL: Brand Stage -->
    <div class="brand-stage">
      <!-- Base Layer / Noise -->
      <div class="noise-overlay"></div>
      
      <!-- Gradient Blobs -->
      <div class="stage-blobs">
        <div class="blob blob-primary"></div>
        <div class="blob blob-success"></div>
      </div>

      <!-- Floating Balance Card -->
      <div class="floating-card-wrapper">
        <div class="glass-card demo-card">
          <div class="demo-card-header">
            <span class="demo-dots">••• 4209</span>
            <span class="badge badge-success">+12.4%</span>
          </div>
          <p class="demo-label">Total Balance</p>
          <h2 class="demo-balance tabular-nums">₱184,200.00</h2>
          
          <div class="demo-chart">
            <svg viewBox="0 0 100 30" class="sparkline" preserveAspectRatio="none">
              <path class="sparkline-path" d="M0,25 C20,20 30,30 40,15 C50,0 70,20 80,10 C90,0 100,5 100,5" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Brand Content -->
      <div class="brand-content">
        <div class="brand-logo">
          <svg class="brand-logo-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <h1 class="brand-name">Alvo</h1>
        </div>
        <h2 class="brand-tagline">Aim. Save. Achieve.</h2>
        
        <!-- Trust badges -->
        <div class="trust-badges">
          <span class="trust-badge">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            256-bit encryption
          </span>
          <span class="trust-badge">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Zero hidden fees
          </span>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL: Auth Zone -->
    <div class="auth-zone">
      <div class="auth-form-container">
        <!-- We mount Clerk's SignIn component -->
        <SignIn :appearance="clerkAppearance" :localization="clerkLocalization" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject, computed } from 'vue'
import { SignIn } from '@clerk/vue'
import { dark } from '@clerk/themes'

const isDark = inject('isDark')

const clerkLocalization = {
  signIn: {
    start: {
      title: 'Welcome to Alvo',
      subtitle: 'Sign in to manage your finances',
    }
  },
  signUp: {
    start: {
      title: 'Create your account',
      subtitle: 'Join Alvo to get started',
    }
  }
}

const clerkAppearance = computed(() => {
  const isDarkMode = isDark.value
  return {
    baseTheme: isDarkMode ? dark : undefined,
    variables: {
      colorPrimary:        isDarkMode ? '#3B82F6' : '#1A56DB',
      colorBackground:     isDarkMode ? '#0F0F11' : '#FFFFFF',
      colorText:           isDarkMode ? '#EDEDED' : '#0F1923',
      colorTextSecondary:  isDarkMode ? '#A1A1AA' : '#5A6478',
      colorInputBackground: isDarkMode ? '#111111' : '#F9FAFB',
      colorInputText:       isDarkMode ? '#EDEDED' : '#0F1923',
      colorDanger:         '#EF4444',
      borderRadius:        '0.75rem',
      fontFamily:          'Geist, Inter, sans-serif',
      spacingUnit:         '1rem'
    },
    layout: {
      socialButtonsPlacement: 'bottom',
      socialButtonsVariant: 'blockButton',
      logoPlacement: 'none',
    },
    elements: {
      rootBox:    { width: '100%', display: 'flex', justifyContent: 'center' },
      cardBox:    { 
        boxShadow: isDarkMode ? '0 4px 24px rgba(0,0,0,0.4)' : '0 12px 32px rgba(0,0,0,0.08)', 
        border: isDarkMode ? '1px solid rgba(255,255,255,0.08)' : '1px solid rgba(0,0,0,0.04)', 
        background: isDarkMode ? '#0F0F11' : '#FFFFFF', 
        padding: '2rem', 
        width: '100%', 
        maxWidth: '420px',
        borderRadius: '1.25rem'
      },
      headerTitle: { 
        fontSize: '1.5rem', 
        fontWeight: '700', 
        color: isDarkMode ? '#EDEDED' : '#0F1923',
        letterSpacing: '-0.02em'
      },
      headerSubtitle: { 
        fontSize: '0.875rem', 
        color: isDarkMode ? '#A1A1AA' : '#5A6478',
        marginTop: '0.5rem'
      },
      socialButtonsBlockButton: {
        border: isDarkMode ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(0,0,0,0.1)',
        backgroundColor: isDarkMode ? '#111111' : '#FFFFFF',
        color: isDarkMode ? '#EDEDED' : '#0F1923',
        borderRadius: '0.75rem',
        transition: 'all 0.15s',
        height: '2.75rem',
        fontWeight: '500'
      },
      formFieldInput: {
        borderRadius: '0.75rem',
        borderColor: isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
        backgroundColor: isDarkMode ? '#111111' : '#F9FAFB',
        height: '2.75rem',
        color: isDarkMode ? '#EDEDED' : '#0F1923',
        transition: 'border-color 0.15s'
      },
      formButtonPrimary: {
        height: '2.75rem',
        borderRadius: '0.75rem',
        fontSize: '0.9375rem',
        fontWeight: '600',
        textTransform: 'none',
        background: 'linear-gradient(135deg, #1A56DB, #4B8EF8)',
        boxShadow: '0 4px 12px rgba(26,86,219,0.25)',
        border: 'none',
        transition: 'transform 0.15s, box-shadow 0.15s'
      },
      footer: {
        background: 'transparent',
        padding: '0',
        marginTop: '1.5rem',
        border: 'none'
      },
      footerAction: {
        background: 'transparent',
        border: 'none',
        padding: '0'
      },
      footerActionText: {
        color: isDarkMode ? '#A1A1AA' : '#5A6478',
      },
      footerActionLink: {
        color: isDarkMode ? '#3B82F6' : '#1A56DB',
        fontWeight: '600'
      },
      dividerLine: {
        background: isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
      },
      dividerText: {
        color: isDarkMode ? '#A1A1AA' : '#5A6478',
      }
    },
  }
})
</script>

<style scoped>
.auth-page {
  display: flex;
  min-height: 100vh;
  width: 100%;
}

/* ── LEFT PANEL: Brand Stage ───────────────────────────────── */
.brand-stage {
  flex: 0 0 55%;
  position: relative;
  background-color: #050505; /* Onyx */
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 4rem;
  color: #fff;
}

@media (max-width: 900px) {
  .brand-stage {
    display: none; /* Hide left panel on small screens */
  }
  .auth-zone {
    flex: 1 !important;
  }
}

.noise-overlay {
  position: absolute;
  inset: 0;
  opacity: 0.03;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
  pointer-events: none;
}

.stage-blobs {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
}

@keyframes blob-drift {
  0%   { transform: translate(0, 0)   scale(1); }
  33%  { transform: translate(30px, -20px) scale(1.05); }
  66%  { transform: translate(-20px, 15px) scale(0.97); }
  100% { transform: translate(0, 0)   scale(1); }
}

.blob-primary {
  width: 50vw;
  height: 50vw;
  top: -10%;
  left: -10%;
  background: #1A56DB;
  opacity: 0.15;
  animation: blob-drift 14s ease-in-out infinite;
}

.blob-success {
  width: 40vw;
  height: 40vw;
  bottom: -10%;
  right: -10%;
  background: #12A179;
  opacity: 0.15;
  animation: blob-drift 18s ease-in-out infinite reverse;
}

/* Floating Card */
.floating-card-wrapper {
  position: absolute;
  top: 20%;
  right: 15%;
  z-index: 10;
  animation: float 6s ease-in-out infinite;
  transform-style: preserve-3d;
}

@keyframes float {
  0%   { transform: translateY(0) rotate(-4deg); }
  50%  { transform: translateY(-15px) rotate(-3deg); }
  100% { transform: translateY(0) rotate(-4deg); }
}

.demo-card {
  background: rgba(17, 17, 17, 0.65) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  width: 320px;
  backdrop-filter: blur(20px) saturate(180%);
}

.demo-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.demo-dots {
  font-size: 0.75rem;
  color: #A1A1AA;
  letter-spacing: 2px;
}

.demo-label {
  font-size: 0.75rem;
  color: #A1A1AA;
  text-transform: uppercase;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.demo-balance {
  font-size: 2rem;
  font-weight: 700;
  color: #EDEDED;
  margin: 0 0 1.5rem 0;
}

.demo-chart {
  height: 40px;
  width: 100%;
}

.sparkline {
  width: 100%;
  height: 100%;
  overflow: visible;
}

@keyframes draw-line {
  from { stroke-dashoffset: 300; }
  to   { stroke-dashoffset: 0; }
}

.sparkline-path {
  stroke-dasharray: 300;
  stroke-dashoffset: 300;
  animation: draw-line 2s cubic-bezier(0.22, 1, 0.36, 1) 0.5s forwards;
}

/* Brand Content */
.brand-content {
  position: relative;
  z-index: 10;
  margin-top: auto;
  margin-bottom: 4rem;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.brand-logo-svg {
  width: 32px;
  height: 32px;
  color: #3B82F6;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.brand-tagline {
  font-size: 3rem;
  font-weight: 300;
  line-height: 1.1;
  letter-spacing: -0.02em;
  margin: 0 0 2rem 0;
  max-width: 400px;
}

.trust-badges {
  display: flex;
  gap: 1.5rem;
}

.trust-badge {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  color: #A1A1AA;
  font-weight: 500;
}

/* ── RIGHT PANEL: Auth Zone ────────────────────────────────── */
.auth-zone {
  flex: 0 0 45%;
  background-color: var(--bg-surface);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.auth-form-container {
  width: 100%;
  max-width: 400px;
}
</style>
