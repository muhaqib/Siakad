<x-app-layout>
    <x-slot name="header">
        AI Academic Advisor
    </x-slot>

    <div class="h-[calc(100vh-120px)] flex flex-col" x-data="aiAdvisor()">
        <!-- Chat Area -->
        <div class="flex-1 overflow-hidden flex flex-col max-w-4xl mx-auto w-full">
            <!-- Messages Container -->
            <div class="flex-1 overflow-y-auto" x-ref="chatContainer">
                <div class="px-4 py-8 space-y-8">
                    <!-- Welcome Message -->
                    <div class="flex gap-4 max-w-3xl mx-auto">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-siakad-primary to-siakad-dark flex items-center justify-center flex-shrink-0 shadow-lg shadow-siakad-primary/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-siakad-dark dark:text-slate-200 leading-relaxed">
                                Halo, <span class="font-semibold">{{ explode(' ', $mahasiswa->user->name)[0] }}</span>. Saya Academic Advisor yang siap membantu Anda dengan pertanyaan seputar akademik — analisis nilai, saran pengambilan KRS, jadwal kuliah, dan lainnya.
                            </p>
                        </div>
                    </div>

                    <!-- Dynamic Messages -->
                    <template x-for="(msg, index) in messages" :key="index">
                        <div class="max-w-3xl mx-auto">
                            <!-- User Message -->
                            <div x-show="msg.role === 'user'" class="flex justify-end mb-4">
                                <div class="bg-siakad-primary text-white px-5 py-3 rounded-2xl rounded-br-md max-w-[80%] shadow-lg shadow-siakad-primary/10">
                                    <p class="text-sm leading-relaxed" x-text="msg.content"></p>
                                </div>
                            </div>
                            
                            <!-- AI Message -->
                            <div x-show="msg.role !== 'user'" class="flex gap-4">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-siakad-primary to-siakad-dark flex items-center justify-center flex-shrink-0 shadow-lg shadow-siakad-primary/20">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                </div>
                                <div class="flex-1 pt-1 prose prose-sm dark:prose-invert max-w-none prose-p:leading-relaxed prose-p:text-siakad-dark dark:prose-p:text-slate-200" x-html="formatMessage(msg.content)"></div>
                            </div>
                        </div>
                    </template>

                    <!-- Thinking Indicator with Status -->
                    <div x-show="isLoading" class="max-w-3xl mx-auto">
                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-siakad-primary to-siakad-dark flex items-center justify-center flex-shrink-0 shadow-lg shadow-siakad-primary/20 animate-pulse">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                            </div>
                            <div class="flex-1 pt-1">
                                <div class="flex items-center gap-2 text-sm text-siakad-secondary dark:text-slate-400">
                                    <span x-text="thinkingStatus"></span>
                                    <span class="flex gap-1">
                                        <span class="w-1.5 h-1.5 bg-siakad-primary rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                        <span class="w-1.5 h-1.5 bg-siakad-primary rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                        <span class="w-1.5 h-1.5 bg-siakad-primary rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                    </span>
                                </div>
                                <div class="mt-2 h-1 w-48 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-siakad-primary to-siakad-accent rounded-full animate-pulse" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area - Fixed at bottom, clean design -->
            <div class="flex-shrink-0 px-4 pb-6 pt-4">
                <div class="max-w-3xl mx-auto">
                    <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-600 overflow-hidden transition-all duration-200 focus-within:ring-2 focus-within:ring-siakad-primary focus-within:border-transparent">
                        <textarea 
                            x-model="input"
                            @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                            :disabled="isLoading"
                            placeholder="Tanyakan sesuatu tentang akademik Anda..." 
                            rows="1"
                            class="w-full px-5 py-4 pr-14 bg-transparent text-siakad-dark dark:text-white placeholder-slate-400 dark:placeholder-slate-400 text-sm resize-none border-0 focus:ring-0 focus:outline-none"
                            style="min-height: 56px; max-height: 150px;"
                            x-ref="inputField"
                            @input="$el.style.height = '56px'; $el.style.height = Math.min($el.scrollHeight, 150) + 'px'"
                        ></textarea>
                        <button 
                            type="button"
                            @click="sendMessage"
                            :disabled="isLoading || !input.trim()"
                            class="absolute right-3 bottom-3 p-2.5 rounded-xl bg-siakad-primary text-white disabled:bg-slate-200 dark:disabled:bg-slate-700 disabled:text-slate-400 dark:disabled:text-slate-500 disabled:cursor-not-allowed hover:bg-siakad-dark transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-center mt-3 text-[11px] text-slate-400 dark:text-slate-500">
                        <span>Enter untuk kirim &nbsp;•&nbsp; Shift+Enter baris baru &nbsp;•&nbsp; Powered by Qwen AI</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function aiAdvisor() {
            return {
                input: '',
                messages: [],
                isLoading: false,
                thinkingStatus: 'Menganalisis pertanyaan',
                thinkingInterval: null,

                startThinking() {
                    const statuses = [
                        'Menganalisis pertanyaan',
                        'Memuat data akademik',
                        'Memeriksa kurikulum',
                        'Menganalisis progress',
                        'Menyusun rekomendasi',
                        'Memverifikasi data',
                        'Menyiapkan jawaban',
                        'Menyelesaikan analisis'
                    ];
                    let index = 0;
                    this.thinkingStatus = statuses[0];
                    this.thinkingInterval = setInterval(() => {
                        if (index < statuses.length - 1) {
                            index++;
                            this.thinkingStatus = statuses[index];
                        }
                        // Stays at last status, doesn't loop back
                    }, 2500);
                },

                stopThinking() {
                    if (this.thinkingInterval) {
                        clearInterval(this.thinkingInterval);
                        this.thinkingInterval = null;
                    }
                },

                async sendMessage() {
                    if (!this.input.trim() || this.isLoading) return;

                    const userMessage = this.input.trim();
                    this.input = '';
                    this.$refs.inputField.style.height = '56px';
                    
                    this.messages.push({ role: 'user', content: userMessage });
                    this.scrollToBottom();
                    
                    this.isLoading = true;
                    this.startThinking();

                    try {
                        const response = await fetch('{{ route("mahasiswa.ai-advisor.chat") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                message: userMessage,
                                history: this.messages.slice(-10),
                            }),
                        });

                        const data = await response.json();
                        
                        this.messages.push({ 
                            role: 'assistant', 
                            content: data.success ? data.message : 'Maaf, terjadi kesalahan: ' + data.message 
                        });
                    } catch (error) {
                        this.messages.push({ 
                            role: 'assistant', 
                            content: 'Maaf, terjadi kesalahan jaringan. Silakan coba lagi.' 
                        });
                    } finally {
                        this.stopThinking();
                        this.isLoading = false;
                        this.scrollToBottom();
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.chatContainer;
                        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                    });
                },

                formatMessage(text) {
                    return text
                        .replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold text-siakad-dark dark:text-white">$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/`(.*?)`/g, '<code class="bg-siakad-light dark:bg-slate-600 px-1.5 py-0.5 rounded text-xs font-mono">$1</code>')
                        .replace(/\n\n/g, '</p><p class="mt-4">')
                        .replace(/\n/g, '<br>')
                        .replace(/^- /gm, '<span class="text-siakad-primary mr-2">•</span>')
                        .replace(/^(\d+)\. /gm, '<span class="font-semibold text-siakad-primary mr-2">$1.</span>');
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
