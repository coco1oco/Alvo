<template>
  <div class="auth-page">
    <!-- LEFT PANEL: Brand Experience -->
    <div class="brand-stage">
      <div class="noise-overlay"></div>
      <div class="brand-glow"></div>

      <!-- Wordmark -->
      <div class="brand-top">
        <div class="brand-mark">
          <img :src="'/logo-dark.svg'" alt="Alvo" class="brand-mark-img" />
          <span class="brand-mark-name">Alvo</span>
        </div>
      </div>

      <!-- Abstract Visual -->
      <div class="brand-visual">
        <svg class="orbital-svg" viewBox="0 0 300 300" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="ga" x1="0" y1="0" x2="300" y2="300" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#D97757"/>
              <stop offset="100%" stop-color="#D97757" stop-opacity="0"/>
            </linearGradient>
            <linearGradient id="gb" x1="300" y1="0" x2="0" y2="300" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#788C5D"/>
              <stop offset="100%" stop-color="#788C5D" stop-opacity="0"/>
            </linearGradient>
          </defs>
          <!-- Static rings -->
          <circle cx="150" cy="150" r="130" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
          <circle cx="150" cy="150" r="95"  stroke="rgba(255,255,255,0.07)" stroke-width="1"/>
          <circle cx="150" cy="150" r="58"  stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
          <!-- Animated arcs -->
          <circle class="arc-a" cx="150" cy="150" r="130"
            stroke="url(#ga)" stroke-width="1.5"
            stroke-dasharray="175 642" stroke-linecap="round"/>
          <circle class="arc-b" cx="150" cy="150" r="95"
            stroke="url(#gb)" stroke-width="1.5"
            stroke-dasharray="100 497" stroke-linecap="round"/>
          <!-- Cardinal ticks -->
          <line x1="150" y1="19"  x2="150" y2="28"  stroke="rgba(255,255,255,0.18)" stroke-width="1" stroke-linecap="round"/>
          <line x1="281" y1="150" x2="272" y2="150" stroke="rgba(255,255,255,0.18)" stroke-width="1" stroke-linecap="round"/>
          <line x1="150" y1="281" x2="150" y2="272" stroke="rgba(255,255,255,0.18)" stroke-width="1" stroke-linecap="round"/>
          <line x1="19"  y1="150" x2="28"  y2="150" stroke="rgba(255,255,255,0.18)" stroke-width="1" stroke-linecap="round"/>
          <!-- Center -->
          <circle cx="150" cy="150" r="3.5" fill="rgba(255,255,255,0.08)"/>
          <circle cx="150" cy="150" r="1.5" fill="#D97757"/>
          <!-- Accent dot (rotates with arc-a) -->
          <circle class="arc-a" cx="150" cy="20" r="3.5" fill="#D97757"
            stroke="rgba(14,13,12,0.8)" stroke-width="1.5"/>
        </svg>
      </div>

      <!-- Headline Copy -->
      <div class="brand-copy">
        <p class="brand-eyebrow">Personal Finance</p>
        <h1 class="brand-headline">
          Your finances,<br>
          <em class="brand-em">at a glance.</em>
        </h1>

      </div>
    </div>

    <!-- RIGHT PANEL: Authentication Panel -->
    <div class="auth-zone">
      <div class="auth-card-container">
        <!-- Custom Header -->
        <div class="auth-header">
          <h3 class="auth-welcome-title">
            {{ isAuthenticating || isSignedIn ? 'Authenticating...' : (isSignUp ? 'Create your account' : 'Welcome Back') }}
          </h3>
          <p class="auth-welcome-subtitle">
            {{ isAuthenticating || isSignedIn ? 'Verifying credentials & preparing your dashboard.' : (isSignUp ? 'Join Alvo to start tracking your finances.' : 'Sign in to continue managing your finances.') }}
          </p>
        </div>

        <!-- Clerk Component with Custom Appearance Overrides OR Loader -->
        <div class="auth-component-wrapper">
          <div v-if="isAuthenticating || isSignedIn" class="auth-loading-card">
            <div class="spinner"></div>
            <p class="text-xs font-semibold text-muted mt-3">Setting up workspace...</p>
          </div>
          <template v-else>
            <SignIn 
              v-if="!isSignUp" 
              routing="virtual" 
              :appearance="clerkAppearance" 
            />
            <SignUp 
              v-else 
              routing="virtual" 
              :appearance="clerkAppearance" 
            />
          </template>
        </div>

        <!-- Custom Mode Switcher Link -->
        <div v-if="!isAuthenticating && !isSignedIn" class="auth-mode-footer">
          <p v-if="!isSignUp" class="mode-text">
            Don't have an account?
            <button type="button" @click="isSignUp = true" class="mode-link">
              Create one →
            </button>
          </p>
          <p v-else class="mode-text">
            Already have an account?
            <button type="button" @click="isSignUp = false" class="mode-link">
              Sign in →
            </button>
          </p>
        </div>

        <!-- Custom Minimal SaaS Footer -->
        <div class="custom-saas-footer">
          <span class="powered-by">Powered by <strong>Clerk</strong></span>
          <div class="footer-links">
            <a href="#" class="footer-link">Privacy</a>
            <span class="link-dot">•</span>
            <a href="#" class="footer-link">Terms</a>
            <span class="link-dot">•</span>
            <a href="#" class="footer-link">Status</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject, ref, computed, watch } from 'vue'
import { SignIn, SignUp, useAuth } from '@clerk/vue'
import { dark } from '@clerk/themes'

const isDark = inject('isDark')

const isSignUp = ref(false)
const isAuthenticating = ref(false)

const { isSignedIn } = useAuth()

watch(isSignedIn, (signedIn) => {
  if (signedIn) {
    isAuthenticating.value = true
  }
}, { immediate: true })

const clerkAppearance = computed(() => {
  const isDarkMode = isDark.value
  return {
    baseTheme: isDarkMode ? dark : undefined,
    variables: {
      colorPrimary:        '#E77B57',
      colorBackground:     'transparent',
      colorText:           isDarkMode ? '#FAF9F5' : '#18181B',
      colorTextSecondary:  isDarkMode ? '#A1A1AA' : '#71717A',
      colorInputBackground: isDarkMode ? '#1E1E1C' : '#FFFFFF',
      colorInputText:       isDarkMode ? '#FAF9F5' : '#18181B',
      colorDanger:         '#E03131',
      borderRadius:        '0.75rem',
      fontFamily:          'Geist, Inter, sans-serif',
      spacingUnit:         '1rem'
    },
    layout: {
      socialButtonsPlacement: 'top',
      socialButtonsVariant: 'blockButton',
      logoPlacement: 'none'
    },
    elements: {
      rootBox: { 
        width: '100%', 
        display: 'flex', 
        justifyContent: 'center',
        boxShadow: 'none',
        border: 'none',
        outline: 'none'
      },
      cardBox: { 
        boxShadow: 'none', 
        border: 'none', 
        outline: 'none',
        background: 'transparent', 
        padding: '0', 
        margin: '0',
        width: '100%', 
        maxWidth: '100%',
        borderRadius: '0'
      },
      card: {
        boxShadow: 'none',
        border: 'none',
        outline: 'none',
        background: 'transparent',
        padding: '0',
        margin: '0'
      },
      header: { display: 'none', margin: '0', padding: '0', height: '0' },
      headerTitle: { display: 'none' },
      headerSubtitle: { display: 'none' },
      socialButtons: { marginTop: '0', paddingTop: '0' },
      socialButtonsBlockButton: {
        border: isDarkMode ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(24,24,27,0.12)',
        backgroundColor: isDarkMode ? '#1E1E1C' : '#FFFFFF',
        color: isDarkMode ? '#FAF9F5' : '#18181B',
        borderRadius: '0.75rem',
        transition: 'all 0.2s cubic-bezier(0.16, 1, 0.3, 1)',
        height: '52px',
        fontWeight: '500',
        fontSize: '0.9375rem',
        boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
        width: '100%',
        marginTop: '0'
      },
      formFieldInput: {
        borderRadius: '0.75rem',
        borderColor: isDarkMode ? 'rgba(255,255,255,0.12)' : 'rgba(24,24,27,0.15)',
        backgroundColor: isDarkMode ? '#1E1E1C' : '#FFFFFF',
        height: '52px',
        padding: '0 1rem',
        color: isDarkMode ? '#FAF9F5' : '#18181B',
        fontSize: '0.9375rem',
        transition: 'border-color 0.2s, box-shadow 0.2s',
        width: '100%',
        boxSizing: 'border-box'
      },
      formButtonPrimary: {
        height: '52px',
        borderRadius: '0.75rem',
        fontSize: '0.9375rem',
        fontWeight: '600',
        color: '#FFFFFF',
        textTransform: 'none',
        background: 'linear-gradient(135deg, #E77B57, #EDA086)',
        boxShadow: '0 4px 16px rgba(231,123,87,0.28)',
        border: 'none',
        transition: 'all 0.2s cubic-bezier(0.16, 1, 0.3, 1)',
        width: '100%'
      },
      footer: {
        display: 'none'
      },
      dividerLine: {
        background: isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(24,24,27,0.1)'
      },
      dividerText: {
        color: isDarkMode ? '#A1A1AA' : '#71717A',
        fontSize: '0.75rem',
        letterSpacing: '0.05em'
      }
    }
  }
})
</script>

<style scoped>
.auth-page {
  display: flex;
  height: 100dvh;
  width: 100%;
  overflow: hidden;
  background-color: var(--bg-base);
}

/* ── LEFT PANEL ─────────────────────────────────────────────── */
.brand-stage {
  flex: 0 0 50%;
  position: relative;
  background-color: #0C0B0A;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 3rem 3.5rem;
  color: #fff;
}

@media (max-width: 900px) {
  .brand-stage { display: none; }
  .auth-zone   { flex: 1 !important; }
}

/* Grain noise */
.noise-overlay {
  position: absolute;
  inset: 0;
  opacity: 0.045;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 1;
}

/* Single ambient warm glow — restrained, not three blobs */
.brand-glow {
  position: absolute;
  width: 60vw;
  height: 60vw;
  top: -20%;
  left: -20%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(217,119,87,0.18) 0%, transparent 65%);
  pointer-events: none;
  z-index: 2;
}

/* Wordmark row */
.brand-top {
  position: relative;
  z-index: 10;
}

.brand-mark {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.brand-mark-img {
  width: 4rem;
  height: 4rem;
  object-fit: contain;
}

.brand-mark-name {
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: rgba(255,255,255,0.85);
}

/* Abstract orbital art */
.brand-visual {
  position: relative;
  z-index: 10;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 0;
}

.orbital-svg {
  width: min(260px, 55%);
  height: auto;
  overflow: visible;
}

@keyframes spin-cw {
  from { transform: rotate(0deg);   }
  to   { transform: rotate(360deg); }
}
@keyframes spin-ccw {
  from { transform: rotate(0deg);    }
  to   { transform: rotate(-360deg); }
}

.arc-a {
  transform-origin: 150px 150px;
  animation: spin-cw 50s linear infinite;
}
.arc-b {
  transform-origin: 150px 150px;
  animation: spin-ccw 35s linear infinite;
}

/* Headline copy */
.brand-copy {
  position: relative;
  z-index: 10;
}

.brand-eyebrow {
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.32);
  margin: 0 0 1rem 0;
}

.brand-headline {
  font-size: clamp(2.25rem, 3.5vw, 3rem);
  font-weight: 800;
  line-height: 1.08;
  letter-spacing: -0.04em;
  margin: 0 0 1rem 0;
  color: #FFFFFF;
  font-style: normal;
}

.brand-em {
  font-style: italic;
  background: linear-gradient(120deg, #D97757 0%, #EDA086 60%, #F7C5AE 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.brand-caption {
  font-size: 0.875rem;
  color: rgba(255,255,255,0.35);
  line-height: 1.65;
  margin: 0;
  font-weight: 400;
}


/* ── RIGHT PANEL: Authentication Panel ───────────────────────── */
.auth-zone {
  flex: 0 0 48%;
  background-color: var(--bg-surface);
  background-image: radial-gradient(rgba(176, 174, 165, 0.12) 1px, transparent 1px);
  background-size: 24px 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 3rem;
  overflow-y: auto;
}

.auth-card-container {
  width: 100%;
  max-width: 460px;
  margin: 0 auto;
}

.auth-header {
  margin-bottom: 1.5rem;
}

.auth-welcome-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.025em;
  margin: 0 0 0.5rem 0;
}

.auth-welcome-subtitle {
  font-size: 0.9375rem;
  color: var(--text-secondary);
  line-height: 1.5;
  margin: 0;
}

.auth-component-wrapper {
  width: 100%;
}

/* Deep overrides to strip any Clerk container borders, outlines, and shadows */
:deep(.cl-rootBox),
:deep(.cl-cardBox),
:deep(.cl-card),
:deep(.cl-main) {
  box-shadow: none !important;
  border: none !important;
  outline: none !important;
  background: transparent !important;
  padding: 0 !important;
  margin: 0 !important;
  overflow: visible !important;
}

:deep(.cl-header),
:deep(.cl-headerTitle),
:deep(.cl-headerSubtitle) {
  display: none !important;
  margin: 0 !important;
  padding: 0 !important;
  height: 0 !important;
}

:deep(.cl-socialButtons) {
  margin-top: 0 !important;
  padding-top: 0 !important;
}

:deep(.cl-formFieldInput),
:deep(.cl-input),
:deep(.cl-formFieldInputGroup) {
  width: 100% !important;
  box-sizing: border-box !important;
  border-radius: 0.75rem !important;
  overflow: visible !important;
}

:deep(.cl-formFieldRow),
:deep(.cl-formField) {
  width: 100% !important;
  overflow: visible !important;
}

.auth-mode-footer {
  margin-top: 1.5rem;
  text-align: center;
}

.mode-text {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
}

.mode-link {
  background: none;
  border: none;
  color: #E77B57;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  margin-left: 0.25rem;
  padding: 0;
  transition: opacity 0.15s;
}

.mode-link:hover {
  opacity: 0.85;
  text-decoration: underline;
}

/* Custom Minimal Footer */
.custom-saas-footer {
  margin-top: 3rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.75rem;
  color: var(--text-muted);
  border-top: 1px solid var(--border);
  padding-top: 1.5rem;
}

.powered-by strong {
  color: var(--text-secondary);
}

.footer-links {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.footer-link {
  color: var(--text-muted);
  text-decoration: none;
  transition: color 0.15s;
}

.footer-link:hover {
  color: var(--text-secondary);
}

.auth-loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  background: var(--bg-surface-2);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  text-align: center;
}

.link-dot {
  opacity: 0.5;
}
</style>
