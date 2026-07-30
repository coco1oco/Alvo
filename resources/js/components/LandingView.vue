<template>
  <div class="landing-page dark">
    <!-- Top Navigation -->
    <nav class="fixed top-6 left-1/2 -translate-x-1/2 rounded-full dark:bg-[var(--bg-surface)]/80 backdrop-blur-xl border border-white/10 shadow-glass z-50 flex justify-between items-center px-6 transition-all duration-300 py-2 w-[95%] max-w-5xl bg-[var(--bg-surface)]/95">
      <div class="flex items-center gap-2.5">
        <img :src="'/logo-dark.svg'" alt="Alvo Logo" class="w-8 h-8 flex-shrink-0 drop-shadow-sm" />
        <span class="font-headline-md text-headline-md font-bold text-[var(--primary)]">Alvo</span>
      </div>
      <div class="hidden md:flex items-center gap-8">
        <a @click.prevent="scrollTo('#features')" class="font-label-md text-label-md text-[var(--text-secondary)] font-medium hover:text-[var(--primary)] transition-colors cursor-pointer" href="#features">Features</a>
        <a @click.prevent="scrollTo('#analytics')" class="font-label-md text-label-md text-[var(--text-secondary)] font-medium hover:text-[var(--primary)] transition-colors cursor-pointer" href="#analytics">Analytics</a>
        <a @click.prevent="scrollTo('#why-alvo')" class="font-label-md text-label-md text-[var(--text-secondary)] font-medium hover:text-[var(--primary)] transition-colors cursor-pointer" href="#why-alvo">Why Alvo</a>
      </div>
      <div class="flex items-center gap-4">
        <button @click="$emit('login', 'signin')" class="btn-ghost">Sign in</button>
        <button @click="$emit('login', 'signup')" class="btn-primary">Get Started</button>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-[884px] pt-40 pb-20 overflow-hidden hero-gradient">
      <div class="max-w-7xl mx-auto px-container-padding text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-panel mb-8 border border-[var(--primary)]/20 hero-stagger-item">
          <span class="w-2 h-2 rounded-full bg-[var(--primary)] animate-pulse"></span>
          <span class="text-label-sm font-label-sm text-[var(--primary)] uppercase tracking-widest">Now in Public Beta</span>
        </div>
        <h1 class="font-headline-lg text-headline-lg max-w-4xl mx-auto mb-6 tracking-tight hero-stagger-item">
          Your finances, at a <span class="text-[var(--primary)] text-glow italic">dead-center glance.</span>
        </h1>
        <p class="font-body-md text-body-md text-[var(--text-secondary)] max-w-2xl mx-auto mb-12 hero-stagger-item">
          Experience high-precision wealth management designed for clarity. Alvo connects your entire financial life into a single, beautiful dashboard.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20 hero-stagger-item">
          <button @click="$emit('login', 'signup')" class="btn-primary px-8 py-4 text-lg w-full sm:w-auto flex justify-center items-center">Start Tracking Free</button>
          <button @click="$emit('login', 'signin')" class="btn-ghost px-8 py-4 text-lg w-full sm:w-auto flex justify-center items-center cursor-pointer">
            Explore Interactive Demo
          </button>
        </div>
        <!-- Dashboard Preview -->
        <div id="hero-preview" class="relative group perspective-1000 hero-stagger-item">
          <div class="absolute -inset-10 bg-[var(--primary)]/20 blur-[100px] rounded-full opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
          <div class="relative rounded-2xl overflow-hidden glass-panel border border-white/10 shadow-2xl floating-dashboard hover:scale-[1.02] transition-transform duration-500">
            <img class="w-full object-cover rounded-2xl" alt="Alvo Dashboard Preview" :src="'/screenshots/dashboard.jpg'" />
          </div>
        </div>
      </div>
    </section>

    <!-- Features Bento Grid -->
    <section id="features" class="py-32 bg-[var(--bg-base)] relative">
      <div class="max-w-7xl mx-auto px-container-padding">
        <div class="mb-16">
          <h2 class="font-headline-lg text-headline-lg mb-4">Precision Engineered Features.</h2>
          <p class="font-body-md text-body-md text-[var(--text-secondary)] max-w-xl">Every tool you need to master your net worth, built with the speed and reliability of modern fintech.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 auto-rows-[390px]">
          <!-- Unified Accounts -->
          <div class="md:col-span-8 glass-panel rounded-3xl overflow-hidden group p-8 flex flex-col md:flex-row items-stretch justify-between gap-6 relative bento-card">
            <div class="w-full md:w-5/12 flex flex-col justify-between relative z-10 pr-2">
              <div>
                <div class="w-12 h-12 bg-[var(--primary)]/10 rounded-xl flex items-center justify-center mb-6 border border-[var(--primary)]/20">
                  <svg class="w-6 h-6 text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V10M5 21V10M12 3L2 10h20L12 3zM3 21h18M9 21v-8m6 8v-8"></path></svg>
                </div>
                <h3 class="font-headline-md text-headline-md mb-3">Unified Accounts</h3>
                <p class="font-body-md text-sm md:text-base text-[var(--text-secondary)] leading-relaxed">Sync all your banks, credit cards, and investment accounts. Real-time data from over 12,000 institutions globally.</p>
              </div>
            </div>
            <div class="w-full md:w-7/12 h-52 md:h-auto rounded-2xl overflow-hidden border border-white/10 shadow-lg relative bg-black/40 flex-shrink-0">
              <img class="w-full h-full object-cover object-left-top transform group-hover:scale-105 transition-transform duration-700" alt="Alvo Accounts Overview" :src="'/screenshots/creditcard.jpg'" />
            </div>
          </div>
          <!-- Smart Tracking -->
          <div class="md:col-span-4 bg-[var(--bg-surface)] rounded-3xl border border-[var(--border)] p-8 flex flex-col group hover:border-[var(--primary)]/40 transition-all duration-300 hover:-translate-y-2 bento-card">
            <div class="mb-6">
              <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center border border-secondary/20 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
            </div>
            <h3 class="font-headline-md text-headline-md mb-2">Smart Tracking</h3>
            <p class="font-body-md text-body-md text-[var(--text-secondary)] mb-auto">Auto-categorization that actually works. We learn your spending habits to provide 99.9% accuracy.</p>
            <div class="mt-6 rounded-xl overflow-hidden border border-white/10 h-36">
              <img class="w-full h-full object-cover object-top opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500" alt="Alvo Transaction Tracking" :src="'/screenshots/transaction.jpg'" />
            </div>
          </div>
          <!-- Insightful Analytics -->
          <div id="analytics" class="md:col-span-5 bg-[var(--bg-surface)] rounded-3xl border border-[var(--border)] p-8 flex flex-col justify-between group hover:border-[var(--primary)]/40 transition-all duration-300 hover:-translate-y-2 relative overflow-hidden bento-card">
            <div class="relative z-10">
              <div class="w-12 h-12 bg-tertiary/10 rounded-xl flex items-center justify-center mb-5 border border-tertiary/20 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
              <h3 class="font-headline-md text-headline-md mb-2">Insightful Analytics</h3>
              <p class="font-body-md text-sm text-[var(--text-secondary)] leading-relaxed">Deep-dive into your financial trends with high-precision charts. Predict future cash flow based on historical data.</p>
            </div>
            <div class="mt-4 h-40 w-full rounded-2xl overflow-hidden border border-white/10 relative z-10 shadow-md bg-black/40">
              <img class="w-full h-full object-cover object-top opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" alt="Alvo Analytics & Subscriptions" :src="'/screenshots/report.jpg'" />
            </div>
          </div>
          <!-- Recurring & Splits -->
          <div class="md:col-span-7 glass-panel rounded-3xl p-8 flex flex-col md:flex-row items-center gap-8 group bento-card">
            <div class="flex-1">
              <div class="w-12 h-12 bg-[var(--primary)]/10 rounded-xl flex items-center justify-center mb-6 border border-[var(--primary)]/20">
                <svg class="w-6 h-6 text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
              </div>
              <h3 class="font-headline-md text-headline-md mb-2">Recurring &amp; Splits</h3>
              <p class="font-body-md text-body-md text-[var(--text-secondary)]">Never miss a subscription. Track Netflix, Spotify, and more. Split shared household expenses with one-click exports.</p>
            </div>
            <div class="flex-1 w-full space-y-3">
              <div class="p-3 bg-[var(--bg-surface)] rounded-xl border border-white/5 flex items-center justify-between transform group-hover:-translate-x-2 transition-all duration-300 hover:bg-white/5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                  <span class="text-sm">Netflix Premium</span>
                </div>
                <span class="text-sm font-bold">$19.99/mo</span>
              </div>
              <div class="p-3 bg-[var(--bg-surface)] rounded-xl border border-white/5 flex items-center justify-between transform group-hover:translate-x-2 transition-all duration-300 hover:bg-white/5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg></div>
                  <span class="text-sm">Fiber Internet</span>
                </div>
                <span class="text-sm font-bold">$70.00/mo</span>
              </div>
              <div class="p-3 bg-[var(--bg-surface)] rounded-xl border border-white/5 flex items-center justify-between transform group-hover:-translate-x-2 transition-all duration-300 hover:bg-white/5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                  <span class="text-sm">Utilities Split</span>
                </div>
                <span class="text-sm font-bold">-$125.40</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Alvo / Core Value Propositions Section -->
    <section id="why-alvo" class="py-32 relative">
      <div class="max-w-7xl mx-auto px-container-padding">
        <div class="flex flex-col lg:flex-row items-center gap-20">
          <div class="lg:w-1/2">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-panel mb-6 border border-[var(--primary)]/20">
              <span class="text-label-sm font-label-sm text-[var(--primary)] uppercase tracking-widest">Designed for You</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg mb-8">Everything you need to master your wealth.</h2>
            <div class="space-y-8">
              <div class="flex items-start gap-6 group value-prop-card">
                <div class="w-14 h-14 bg-[var(--primary)]/20 rounded-2xl flex-shrink-0 flex items-center justify-center border border-[var(--primary)]/30 group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-7 h-7 text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                  <h4 class="font-headline-md text-headline-md mb-2">Total Net Worth Pulse</h4>
                  <p class="font-body-md text-body-md text-[var(--text-secondary)]">Consolidate bank accounts, credit cards, investments, and debts into a single, real-time live balance metric.</p>
                </div>
              </div>
              <div class="flex items-start gap-6 group value-prop-card">
                <div class="w-14 h-14 bg-secondary/20 rounded-2xl flex-shrink-0 flex items-center justify-center border border-secondary/30 group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-7 h-7 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <div>
                  <h4 class="font-headline-md text-headline-md mb-2">Smart Budget Guard</h4>
                  <p class="font-body-md text-body-md text-[var(--text-secondary)]">Proactive alerts notify you before you hit spending limits so you stay on track toward your financial milestones.</p>
                </div>
              </div>
              <div class="flex items-start gap-6 group value-prop-card">
                <div class="w-14 h-14 bg-tertiary/20 rounded-2xl flex-shrink-0 flex items-center justify-center border border-tertiary/30 group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-7 h-7 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                  <h4 class="font-headline-md text-headline-md mb-2">Automated Financial Reports</h4>
                  <p class="font-body-md text-body-md text-[var(--text-secondary)]">Export clean, structured expense reports and income summaries ready for tax season or financial advisors.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="lg:w-1/2 w-full">
            <div class="relative p-1 glass-panel rounded-[2rem] overflow-hidden group">
              <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent group-hover:opacity-80 transition-opacity"></div>
              <div class="bg-black/40 p-10 rounded-[1.9rem] border border-white/5 relative z-10 transition-all duration-500 group-hover:bg-black/20">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/10">
                  <div>
                    <div class="text-xs uppercase tracking-wider text-[var(--text-secondary)]">Live Net Worth Overview</div>
                    <div class="text-3xl font-bold text-white mt-1">$142,850.00</div>
                  </div>
                  <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-semibold">+12.4% this year</span>
                </div>
                <div class="space-y-4">
                  <div class="flex justify-between items-center text-sm p-3 bg-white/5 rounded-xl border border-white/5">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[var(--primary)]"></span> Liquid Cash</span>
                    <span class="font-semibold text-white">$45,200.00</span>
                  </div>
                  <div class="flex justify-between items-center text-sm p-3 bg-white/5 rounded-xl border border-white/5">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-secondary"></span> Investments &amp; Stocks</span>
                    <span class="font-semibold text-white">$112,650.00</span>
                  </div>
                  <div class="flex justify-between items-center text-sm p-3 bg-white/5 rounded-xl border border-white/5">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-400"></span> Total Liabilities</span>
                    <span class="font-semibold text-red-400">-$15,000.00</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Final CTA -->
    <section class="py-32 relative overflow-hidden">
      <div class="absolute inset-0 bg-[var(--primary)]/5"></div>
      <div class="max-w-4xl mx-auto px-container-padding text-center relative z-10">
        <h2 class="font-headline-lg text-headline-lg mb-8">Hit your financial target with Alvo.</h2>
        <p class="font-body-md text-body-md text-[var(--text-secondary)] mb-12">Join thousands of users who have found financial clarity through our high-precision engine.</p>
        <div class="bg-[var(--bg-surface)]/50 backdrop-blur-md rounded-[2.5rem] p-12 border border-[var(--primary)]/20 shadow-glass-strong relative overflow-hidden group hover:border-[var(--primary)]/40 transition-all duration-500">
          <div class="relative z-10">
            <h3 class="font-headline-md text-headline-md mb-8">Ready to take control?</h3>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
              <button @click="$emit('login', 'signup')" class="bg-[var(--primary)] text-white px-10 py-5 rounded-2xl font-headline-md shadow-glass hover:opacity-90 hover:scale-105 active:scale-95 transition-all duration-300 w-full sm:w-auto interactive-cta">
                Create Free Account
              </button>
              <p class="text-label-sm text-[var(--text-secondary)]">No credit card required. <br />3-minute setup.</p>
            </div>
          </div>
          <!-- Abstract visual behind CTA -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-[var(--primary)]/10 blur-[80px] rounded-full -translate-y-1/2 translate-x-1/2 group-hover:bg-[var(--primary)]/20 transition-all duration-700"></div>
          <div class="absolute bottom-0 left-0 w-48 h-48 bg-[var(--primary)]/5 blur-[60px] rounded-full translate-y-1/2 -translate-x-1/2"></div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="w-full py-12 bg-background/50 border-t border-[var(--border)] relative z-10">
      <div class="max-w-7xl mx-auto px-gutter flex flex-col md:flex-row justify-between items-center gap-stack-md">
        <div class="flex flex-col items-center md:items-start gap-4">
          <div class="flex items-center gap-2.5">
            <img :src="'/logo-dark.svg'" alt="Alvo Logo" class="w-7 h-7 flex-shrink-0 opacity-90" />
            <span class="font-headline-sm text-headline-sm font-bold text-[var(--primary)]">Alvo</span>
          </div>
          <p class="font-label-sm text-label-sm text-[var(--text-secondary)] max-w-sm text-center md:text-left opacity-80">© 2026 Alvo Financial by <a href="https://kurtmichaelmirafelix.dev/" target="_blank" rel="noopener noreferrer" class="hover:underline text-[var(--primary)] font-semibold">Kurt Michael Mirafelix</a>. All rights reserved.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-8">
          <button @click="showPrivacyModal = true" class="font-label-sm text-label-sm text-[var(--text-secondary)] hover:text-[var(--primary)] transition-all opacity-80 hover:opacity-100 cursor-pointer">Privacy Policy</button>
          <button @click="showTermsModal = true" class="font-label-sm text-label-sm text-[var(--text-secondary)] hover:text-[var(--primary)] transition-all opacity-80 hover:opacity-100 cursor-pointer">Terms of Service</button>
          <a class="font-label-sm text-label-sm text-[var(--text-secondary)] hover:text-[var(--primary)] transition-all opacity-80 hover:opacity-100" href="https://kurtmichaelmirafelix.dev/" target="_blank" rel="noopener noreferrer">Contact</a>
        </div>
      </div>
    </footer>

    <!-- Privacy Policy Modal -->
    <div v-if="showPrivacyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" @click.self="showPrivacyModal = false">
      <div class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto bg-[var(--bg-surface)] border border-white/10 rounded-3xl p-8 shadow-2xl text-left">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-white/10">
          <div class="flex items-center gap-3">
            <img :src="'/logo-dark.svg'" alt="Alvo Logo" class="w-6 h-6" />
            <h3 class="text-xl font-bold text-white">Alvo Privacy Policy</h3>
          </div>
          <button @click="showPrivacyModal = false" class="text-gray-400 hover:text-white transition-colors cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div class="space-y-4 text-sm text-[var(--text-secondary)] leading-relaxed">
          <p><strong class="text-white">Effective Date:</strong> July 30, 2026</p>
          <p>At <strong>Alvo Finance Manager</strong>, your financial privacy is our highest priority. This Privacy Policy outlines how we handle your personal and financial information when you use our platform.</p>
          
          <h4 class="text-base font-semibold text-white mt-4">1. Information We Collect</h4>
          <p>We process information required to manage your personal finances, including:</p>
          <ul class="list-disc pl-5 space-y-1">
            <li><strong>Account Authentication:</strong> Handled securely via Clerk (email address, user identity tokens).</li>
            <li><strong>Financial Records:</strong> Transaction logs, wallet balances, budgets, debt splits, and category preferences stored in encrypted database tables.</li>
          </ul>

          <h4 class="text-base font-semibold text-white mt-4">2. Data Security &amp; Encryption</h4>
          <p>We employ industry-standard encryption standards for data in transit and at rest. Your financial transactions and net worth metrics are strictly accessible only to your authenticated account.</p>

          <h4 class="text-base font-semibold text-white mt-4">3. Third-Party Services</h4>
          <p>Alvo integrates with Clerk for secure authentication. We never sell, rent, or trade your personal or financial data to third-party advertisers.</p>

          <h4 class="text-base font-semibold text-white mt-4">4. Your Control &amp; Rights</h4>
          <p>You retain full ownership of your financial data. You can export your transaction records or delete your account data at any time via Settings.</p>
        </div>
        <div class="mt-8 pt-4 border-t border-white/10 flex justify-end">
          <button @click="showPrivacyModal = false" class="btn-primary px-6 py-2 cursor-pointer">Close</button>
        </div>
      </div>
    </div>

    <!-- Terms of Service Modal -->
    <div v-if="showTermsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" @click.self="showTermsModal = false">
      <div class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto bg-[var(--bg-surface)] border border-white/10 rounded-3xl p-8 shadow-2xl text-left">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-white/10">
          <div class="flex items-center gap-3">
            <img :src="'/logo-dark.svg'" alt="Alvo Logo" class="w-6 h-6" />
            <h3 class="text-xl font-bold text-white">Alvo Terms of Service</h3>
          </div>
          <button @click="showTermsModal = false" class="text-gray-400 hover:text-white transition-colors cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div class="space-y-4 text-sm text-[var(--text-secondary)] leading-relaxed">
          <p><strong class="text-white">Last Updated:</strong> July 30, 2026</p>
          <p>By accessing or using <strong>Alvo Finance Manager</strong>, you agree to be bound by these Terms of Service.</p>
          
          <h4 class="text-base font-semibold text-white mt-4">1. Use of Service</h4>
          <p>Alvo is provided for personal financial tracking and wealth management purposes. You are responsible for keeping your login credentials confidential.</p>

          <h4 class="text-base font-semibold text-white mt-4">2. Financial Disclaimer</h4>
          <p>Alvo provides financial tracking tools, automated calculations, and reporting for informational purposes only. Alvo does not provide certified tax, legal, or investment advice.</p>

          <h4 class="text-base font-semibold text-white mt-4">3. Platform Availability &amp; Modifications</h4>
          <p>We strive for continuous uptime and system reliability. Features and performance metrics may be updated periodically to enhance system precision.</p>

          <h4 class="text-base font-semibold text-white mt-4">4. Limitation of Liability</h4>
          <p>Alvo shall not be liable for indirect, incidental, or consequential damages resulting from your reliance on financial records or third-party bank connections.</p>
        </div>
        <div class="mt-8 pt-4 border-t border-white/10 flex justify-end">
          <button @click="showTermsModal = false" class="btn-primary px-6 py-2 cursor-pointer">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const emit = defineEmits(['login'])

const showPrivacyModal = ref(false)
const showTermsModal = ref(false)

const scrollTo = (selector) => {
  const el = document.querySelector(selector)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' })
  }
}

const handleScroll = () => {
  const nav = document.querySelector('nav')
  if (!nav) return
  if (window.scrollY > 50) {
    nav.classList.add('py-2', 'w-[95%]', 'max-w-5xl', 'bg-[var(--bg-surface)]/95')
    nav.classList.remove('py-3', 'w-[90%]', 'max-w-4xl', 'bg-[var(--bg-surface)]/80')
  } else {
    nav.classList.remove('py-2', 'w-[95%]', 'max-w-5xl', 'bg-[var(--bg-surface)]/95')
    nav.classList.add('py-3', 'w-[90%]', 'max-w-4xl', 'bg-[var(--bg-surface)]/80')
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)

  // GSAP 1: Hero Staggered Entrance
  gsap.from('.hero-stagger-item', {
    y: 40,
    opacity: 0,
    duration: 1,
    stagger: 0.15,
    ease: 'power3.out'
  })

  // GSAP 2: 3D Scroll Parallax Tilt on Hero Dashboard
  gsap.to('.floating-dashboard', {
    rotateX: 0,
    scale: 1.03,
    scrollTrigger: {
      trigger: '.hero-gradient',
      start: 'top top',
      end: 'bottom top',
      scrub: 1
    }
  })

  // GSAP 3: Bento Grid Cards Batch Scroll Reveal
  ScrollTrigger.batch('.bento-card', {
    onEnter: (batch) => {
      gsap.fromTo(batch, 
        { opacity: 0, y: 40, scale: 0.95 },
        {
          opacity: 1,
          y: 0,
          scale: 1,
          duration: 0.8,
          stagger: 0.15,
          ease: 'power2.out',
          overwrite: true
        }
      )
    },
    start: 'top 85%'
  })

  // GSAP 4: Value Prop Cards Reveal
  ScrollTrigger.batch('.value-prop-card', {
    onEnter: (batch) => {
      gsap.fromTo(batch,
        { opacity: 0, x: -30 },
        {
          opacity: 1,
          x: 0,
          duration: 0.7,
          stagger: 0.15,
          ease: 'power2.out',
          overwrite: true
        }
      )
    },
    start: 'top 85%'
  })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  ScrollTrigger.getAll().forEach(t => t.kill())
})
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background-color: var(--bg-base);
  color: var(--text-primary);
}

.text-headline-lg { font-size: 48px; line-height: 56px; font-weight: 800; }
.text-headline-md { font-size: 24px; line-height: 32px; font-weight: 700; }
.text-headline-sm { font-size: 18px; line-height: 24px; font-weight: 700; }
.text-body-md { font-size: 16px; line-height: 24px; font-weight: 400; }
.text-label-md { font-size: 14px; line-height: 20px; font-weight: 600; }
.text-label-sm { font-size: 12px; line-height: 16px; font-weight: 500; }

.font-headline-lg, .font-headline-md, .font-headline-sm, .font-body-md, .font-label-md, .font-label-sm {
  font-family: var(--font-sans);
}

.glass-panel {
  background: rgba(25, 25, 25, 0.6);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.glass-panel:hover {
  border-color: rgba(217, 119, 87, 0.4);
  box-shadow: 0 0 30px rgba(217, 119, 87, 0.1);
  transform: translateY(-4px);
}

.hero-gradient {
  background: radial-gradient(circle at 50% -20%, rgba(217, 119, 87, 0.15) 0%, rgba(10, 10, 10, 0) 60%);
}

.floating-dashboard {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0% { transform: translateY(0px) rotateX(5deg); }
  50% { transform: translateY(-20px) rotateX(2deg); }
  100% { transform: translateY(0px) rotateX(5deg); }
}

.text-glow {
  text-shadow: 0 0 20px rgba(217, 119, 87, 0.4);
}

.interactive-cta {
  position: relative;
  overflow: hidden;
}

.interactive-cta::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
  transform: translateX(-100%);
  transition: transform 0.6s ease;
}

.interactive-cta:hover::after {
  transform: translateX(100%);
}
</style>
