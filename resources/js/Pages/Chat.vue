<template>
    <div class="chat-app" :class="{ 'has-attachments': attachments.length }">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ hidden: isNarrow && activeChat }">
            <div class="search-bar">
                <input v-model="query" type="text" placeholder="Search" @keydown.stop/>
            </div>

            <div class="filter-tabs">
                <button class="tab-btn" :class="{ active: currentTab === 'inbox' }" @click="currentTab = 'inbox'">
                    <font-awesome-icon :icon="['fas', 'inbox']" class="att-icon"/>
                    Messages
                </button>
                <button class="tab-btn" :class="{ active: currentTab === 'archive' }" @click="currentTab = 'archive'">
                    <font-awesome-icon :icon="['fas', 'box-archive']" class="att-icon"/>
                    Archive
                </button>
            </div>

            <ul class="chat-list">
                <li
                    v-for="chat in filteredChats"
                    :key="chat.id"
                    class="chat-item"
                    :class="{ active: activeChat && activeChat.id === chat.id }"
                    @click="openChat(chat)"
                >
                    <div class="avatar">
                        <img v-if="chat.avatar" :src="chat.avatar" alt=""/>
                        <div v-else class="avatar-fallback">{{ chat.initials }}</div>
                    </div>

                    <div class="meta">
                        <div class="top">
                            <span class="name">{{ chat.name }}</span>
                            <span class="time">{{ formatTime(chat.lastMessageAt) }}</span>
                        </div>
                        <div class="preview">
                            <span class="text">{{ chat.lastMessage }}</span>
                            <span v-if="chat.unreadCount" class="badge">{{ chat.unreadCount }}</span>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- Pager (özel 3-lük pencere) -->
            <nav class="pager" v-if="currentTab ==='archive'">
                <button
                    class="pager-btn square arrow"
                    :disabled="!prevUrl"
                    @click="goTo(prevUrl)"
                    aria-label="Previous"
                >‹
                </button>

                <button
                    v-for="p in pagerPages"
                    :key="p.page"
                    class="pager-btn square"
                    :class="{ active: p.active }"
                    :disabled="!p.url"
                    @click="goTo(p.url)"
                >{{ p.page }}
                </button>

                <button
                    class="pager-btn square arrow"
                    :disabled="!nextUrl"
                    @click="goTo(nextUrl)"
                    aria-label="Next"
                >›
                </button>
            </nav>
        </aside>

        <main class="conversation" :class="{ hidden: isNarrow && !activeChat }">
            <div class="conv-header" v-if="activeChat">
                <button class="back-btn" v-if="isNarrow" @click="activeChat = null" aria-label="Back">←</button>
                <div class="peer">
                    <div class="avatar small">
                        <img v-if="activeChat.avatar" :src="activeChat.avatar" alt=""/>
                        <div v-else class="avatar-fallback">{{ activeChat.initials }}</div>
                    </div>
                    <div class="peer-meta">
                        <h2>{{ activeChat.name }}</h2>
                    </div>
                </div>
            </div>

            <div v-if="activeChat" class="messages" ref="messagesEl">
                <div
                    v-for="(m, i) in (activeChat.messages || [])"
                    :key="m.id"
                    class="message-row"
                    :class="{ mine: m.from === 'me' }"
                >
                    <div class="bubble">
                        <div class="text" v-if="m.text" v-html="decorateLinks(m.text)"></div>

                        <div v-if="m.attachments && m.attachments.length" class="att-list">
                            <div v-for="(a, idx) in m.attachments" :key="idx" class="att-item">
                                <a v-if="a.previewUrl" :href="a.url || a.previewUrl" :download="a.name"
                                   class="att-thumb-link">
                                    <img :src="a.previewUrl" :alt="a.name" class="att-thumb"/>
                                </a>

                                <a v-else class="att-file" :href="a.url" :download="a.name"
                                   :title="`${a.name} • ${prettySize(a.size)}`">
                                    <font-awesome-icon :icon="iconFor(a.type, a.name)" class="att-icon"/>
                                    <div class="att-meta">
                                        <div class="att-name">{{ a.name }}</div>
                                        <div class="att-size">{{ prettySize(a.size) }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="stamp">{{ formatTime(m.at) }}</div>
                    </div>
                </div>

                <div v-if="activeChat.typing" class="typing">
                    <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                </div>
            </div>

            <div v-else class="placeholder">
                Sohbet seç
            </div>

            <div v-if="activeChat" class="composer">
        <textarea
            v-model="draft"
            placeholder="Type a message"
            @keydown.enter.exact.prevent="send"
            @keydown.enter.shift.stop
        ></textarea>

                <div class="actions">
                    <button class="icon-btn" title="Attach" aria-label="Attach" @click="pickFiles">
                        <font-awesome-icon :icon="['fas', 'paperclip']"/>
                    </button>
                    <button class="icon-btn" title="Send" aria-label="Send" @click="send">
                        <font-awesome-icon :icon="['fas', 'paper-plane']"/>
                    </button>
                </div>

                <input
                    ref="fileInput"
                    type="file"
                    multiple
                    @change="onFilesSelected"
                    accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt"
                    style="display:none"
                />

                <div v-if="attachments.length" class="attach-list">
                    <div
                        v-for="(f, i) in attachments"
                        :key="i"
                        class="attach-chip"
                        :title="`${f.name} • ${prettySize(f.size)}`"
                    >
                        <span class="chip-name">{{ f.name }}</span>
                        <button class="chip-remove" @click="removeAttachment(i)" aria-label="Remove">✕</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
export default {
    name: "ChatPage",
    props: {
        // Laravel paginator: { data, links, current_page, last_page, path, prev_page_url, next_page_url ... }
        conversations: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            isNarrow: false,
            query: "",
            currentTab: "inbox", // 'inbox' | 'archive'
            draft: "",
            attachments: [],
            maxFileSize: 25 * 1024 * 1024, // 25 MB
            activeChat: null,
            fakeReplyTimer: null,
            loadingMessages: false,       // <-- mesaj çekme durumu
        };
    },
    computed: {
        filteredChats() {
            const q = this.query.trim().toLowerCase();
            const wantArchived = this.currentTab === "archive";
            const list = this.conversations?.data || [];
            return list
                .filter(
                    (c) =>
                        c.archived === wantArchived &&
                        ((c.name && c.name.toLowerCase().includes(q)) ||
                            (c.lastMessage && c.lastMessage.toLowerCase().includes(q)))
                )
                .sort((a, b) => (b.lastMessageAt || 0) - (a.lastMessageAt || 0));
        },

        // Prev/Next URL'leri (ok butonları)
        prevUrl() {
            return this.conversations?.prev_page_url || null;
        },
        nextUrl() {
            return this.conversations?.next_page_url || null;
        },

        // links[] -> { [pageNumber]: url }
        numericLinkMap() {
            const map = {};
            (this.conversations?.links || []).forEach((l) => {
                const n = Number(l.label);
                if (!Number.isNaN(n)) map[n] = l.url;
            });
            return map;
        },

        // 5 sayfalık pencere (istersen 3'e çekebilirsin: const size = 3)
        pagerPages() {
            const current = this.conversations?.current_page || 1;
            const last = this.conversations?.last_page || 1;
            const size = 5;

            let start = Math.max(1, current - 1);
            start = Math.min(start, Math.max(1, last - size + 1));
            const end = Math.min(last, start + size - 1);

            const pages = [];
            for (let p = start; p <= end; p++) {
                pages.push({
                    page: p,
                    url: this.urlForPage(p),
                    active: p === current
                });
            }
            return pages;
        }
    },
    watch: {
        activeChat(newVal) {
            if (!newVal) return;
            if (!Array.isArray(newVal.messages)) newVal.messages = [];
            this.$nextTick(this.scrollToBottom);
            newVal.unreadCount = 0;
        },
        "activeChat.messages": {
            handler() {
                this.$nextTick(this.scrollToBottom);
            },
            deep: true
        }
    },
    async mounted() {
        // URL'den tab/search geri yükleme (opsiyonel)
        const sp = new URLSearchParams(window.location.search);
        const tab = sp.get("tab");
        const q = sp.get("q");
        if (tab === "archive" || tab === "inbox") this.currentTab = tab;
        if (q) this.query = q;

        // İlk aktif chat
        const first =
            (this.conversations?.data || []).find((c) => !c.archived) ||
            (this.conversations?.data || [])[0];

        if (first) {
            await this.setActiveChatAndLoad(first); // <-- mesajları yükle
        }

        this.onResize();
        window.addEventListener("resize", this.onResize);
        this.$nextTick(this.scrollToBottom);
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.onResize);
        if (this.fakeReplyTimer) clearTimeout(this.fakeReplyTimer);
    },
    methods: {
        // CHAT AÇMA: aktif yap + mesajları çek
        async openChat(chat) {
            await this.setActiveChatAndLoad(chat);
        },

        async setActiveChatAndLoad(chat, page = 1) {
            // Liste öğesini referans alalım, messages yoksa ekleyelim
            if (!Array.isArray(chat.messages)) chat.messages = [];
            this.activeChat = chat;

            // source/id yoksa (örn. DTO güncellenmemişse) fetch yapılamaz
            if (!chat?.source || typeof chat.id === "undefined") return;

            await this.loadMessages(chat, page);
        },

        // Verilen sayfa için URL üret (links[] yoksa path?page=N)
        urlForPage(page) {
            const fromLinks = this.numericLinkMap[page] || null;
            if (fromLinks) return fromLinks;
            const base = this.conversations?.path || window.location.pathname;
            const u = new URL(base, window.location.origin);
            u.searchParams.set("page", page);
            return u.toString();
        },

        // Pagination navigation — query & tab korunur
        goTo(url) {
            if (!url) return;
            try {
                const u = new URL(url, window.location.href);
                if (this.query) u.searchParams.set("q", this.query);
                if (this.currentTab) u.searchParams.set("tab", this.currentTab);
                this.$inertia.visit(u.toString(), {
                    preserveScroll: true,
                    preserveState: true
                });
            } catch (_) {
                this.$inertia.visit(url, {preserveScroll: true, preserveState: true});
            }
        },

        // Mesajları backend'den çek ve activeChat'e uygula
        async loadMessages(chat, page = 1) {
            try {
                this.loadingMessages = true;

                const url = `/chat/messages/${encodeURIComponent(chat.source)}/${encodeURIComponent(
                    chat.id
                )}?page=${page}`;

                const res = await fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json"
                    },
                    credentials: "same-origin"
                });

                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const payload = await res.json();

                // Laravel Resource + Paginator yapısı: { data: [...], links: {...}, meta: {...} }
                const list = Array.isArray(payload?.data) ? payload.data : [];
                const meta = payload?.meta || {};
                const links = payload?.links || {};

                // UI'da eski->yeni sıralama kullanıyoruz, o yüzden ters çeviriyoruz
                const ascending = [...list].reverse();

                // Eğer sayfa 1 ise komple set; başka sayfa ise "daha eskileri" başa ekle
                if (page === 1 || !Array.isArray(chat.messages) || chat.messages.length === 0) {
                    chat.messages = ascending;
                } else {
                    chat.messages = [...ascending, ...chat.messages];
                }

                // (İstersen chat üzerinde meta tut)
                chat._msgMeta = {
                    current_page: meta.current_page,
                    last_page: meta.last_page,
                    prev: links.prev || null,
                    next: links.next || null,
                    per_page: meta.per_page,
                    total: meta.total
                };
            } catch (e) {
                console.error("loadMessages failed:", e);
            } finally {
                this.loadingMessages = false;
                this.$nextTick(this.scrollToBottom);
            }
        },

        // Gerekirse eski mesajları yüklemek için çağır (infinite scroll'a hazır)
        async loadOlderMessages() {
            const chat = this.activeChat;
            if (!chat?._msgMeta) return;
            const nextPage =
                (chat._msgMeta.current_page || 1) + 1;
            if (chat._msgMeta.last_page && nextPage > chat._msgMeta.last_page) return;
            await this.loadMessages(chat, nextPage);
        },

        pickFiles() {
            this.$refs.fileInput?.click();
        },

        onFilesSelected(e) {
            const files = Array.from(e.target.files || []);
            const accepted = files.filter((f) => f.size <= this.maxFileSize);

            const mapped = accepted.map((f) => {
                const type = f.type || this.guessMimeByName(f.name);
                const isImage = /^image\//.test(type);
                return {
                    file: f,
                    name: f.name,
                    size: f.size,
                    type,
                    previewUrl: isImage ? URL.createObjectURL(f) : null
                };
            });

            this.attachments.push(...mapped);
            e.target.value = null;
        },

        removeAttachment(index) {
            const a = this.attachments[index];
            if (a?.previewUrl) URL.revokeObjectURL(a.previewUrl);
            this.attachments.splice(index, 1);
        },

        send() {
            const text = this.draft.trim();
            if (!text && this.attachments.length === 0) return;
            if (!this.activeChat) return;

            const now = Date.now();

            const atts = this.attachments.map((a) => {
                const downloadUrl = a.previewUrl || URL.createObjectURL(a.file);
                return {
                    name: a.name,
                    size: a.size,
                    type: a.type,
                    previewUrl: a.previewUrl || null,
                    url: downloadUrl
                };
            });

            this.activeChat.messages.push({
                id: this.randId(),
                from: "me",
                text: text || "(dosya gönderildi)",
                at: now,
                attachments: atts
            });
            this.activeChat.lastMessage = text || "📎 Dosya gönderildi";
            this.activeChat.lastMessageAt = now;

            this.draft = "";
            this.attachments = [];

            if (this.fakeReplyTimer) clearTimeout(this.fakeReplyTimer);
            this.fakeReplyTimer = setTimeout(() => {
                if (!this.activeChat) return;
                const replyNow = Date.now();
                this.activeChat.messages.push({
                    id: this.randId(),
                    from: "peer",
                    text: "Dosyaları aldım, teşekkürler.",
                    at: replyNow
                });
                this.activeChat.lastMessage = "Dosyaları aldım, teşekkürler.";
                this.activeChat.lastMessageAt = replyNow;
            }, 700);
        },

        scrollToBottom() {
            const el = this.$refs.messagesEl;
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatTime(ts) {
            if (!ts && ts !== 0) return "";
            const d = new Date(ts);
            return d.toLocaleTimeString([], {hour: "2-digit", minute: "2-digit"});
        },

        initials(name) {
            return name
                .split(" ")
                .map((p) => p[0])
                .join("")
                .slice(0, 2)
                .toUpperCase();
        },

        showSender(index) {
            return index === 0;
        },

        onResize() {
            this.isNarrow = window.innerWidth < 992;
        },

        randId() {
            return Math.random().toString(36).slice(2, 10);
        },

        prettySize(bytes) {
            if (bytes < 1024) return `${bytes} B`;
            if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        },

        guessMimeByName(name) {
            const ext = name.split(".").pop()?.toLowerCase();
            const map = {
                jpg: "image/jpeg",
                jpeg: "image/jpeg",
                png: "image/png",
                webp: "image/webp",
                gif: "image/gif",
                pdf: "application/pdf",
                doc: "application/msword",
                docx: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                xls: "application/vnd.ms-excel",
                xlsx: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                zip: "application/zip",
                rar: "application/vnd.rar",
                txt: "text/plain",
                mp4: "video/mp4",
                mov: "video/quicktime"
            };
            return map[ext] || "application/octet-stream";
        },

        iconFor(type, name) {
            const t = (type || this.guessMimeByName(name)).toLowerCase();
            if (t.startsWith("image/")) return ["fas", "file-image"];
            if (t.startsWith("video/")) return ["fas", "file-video"];
            if (t.includes("pdf")) return ["fas", "file-pdf"];
            if (t.includes("sheet") || /xls/.test(t)) return ["fas", "file-excel"];
            if (t.includes("word") || /doc/.test(t)) return ["fas", "file-word"];
            if (t.includes("zip") || /rar/.test(t)) return ["fas", "file-zipper"];
            return ["fas", "file"];
        },
        decorateLinks(html = '') {
            if (!html) return '';


            const wrapper = document.createElement('div');
            wrapper.innerHTML = String(html);

            wrapper.querySelectorAll('a[href]').forEach((a) => {

                a.style.color = '#F4D06F';
                a.style.textDecoration = 'none';

                if (!a.target) a.target = '_blank';
                if (!a.rel) a.rel = 'noopener noreferrer';

                const hasBold = a.querySelector('b, strong');
                const hasUnderline = a.querySelector('u');

                if (!hasBold) {
                    const b = document.createElement('b');
                    b.innerHTML = a.innerHTML;
                    a.innerHTML = '';
                    a.appendChild(b);
                }
                if (!hasUnderline) {
                    const u = document.createElement('u');
                    u.innerHTML = a.innerHTML;
                    a.innerHTML = '';
                    a.appendChild(u);
                }
            });

            // sanitize YOK — direkt HTML döndürüyoruz
            return wrapper.innerHTML;
        }
    }
};
</script>


<style scoped>
:root {
    --bg: #f6f7f9;
    --panel: #ffffff;
    --muted: #6b7280;
    --border: #e5e7eb;
    --blue: #1e73e8;
    --active: #356A99;
}

/* Layout */
.chat-app {
    display: grid;
    grid-template-columns: 320px 1fr;
    height: 100dvh;
    background: var(--bg);
    overflow: hidden;
}

.sidebar {
    border-right: 1px solid var(--border);
    background: var(--panel);
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.sidebar.hidden {
    display: none;
}

/* Sidebar üst kısımlar */
.search-bar {
    padding: 12px;
    border-bottom: 1px solid var(--border);
}

.search-bar input {
    width: 100%;
    height: 40px;
    border-radius: 10px;
    border: 1px solid var(--border);
    padding: 0 12px;
    outline: none;
    background: #f8fafc;
}

.filter-tabs {
    display: flex;
    gap: 8px;
    padding: 8px 12px;
    border-bottom: 1px solid var(--border);
}

.filter-tabs .att-icon {
    margin-right: 6px;
    font-size: 14px;
    color: #475569;
    transition: color .15s ease;
}

.filter-tabs .tab-btn.active .att-icon {
    color: #fff;
}

.filter-tabs .tab-btn:not(.active):hover .att-icon {
    color: #334155;
}

.tab-btn {
    flex: 1;
    height: 34px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #fff;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    color: #475569;
}

.tab-btn.active {
    background: #1e72e8;
    color: #fff;
    border-color: var(--active);
}

.tab-btn:not(.active):hover {
    border-color: #94a3b8;
}

.chat-list {
    list-style: none;
    margin: 0;
    padding: 8px;
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    padding-right: 8px;
    overscroll-behavior: contain;
}

/* İsteğe bağlı: scrollbar stili */
.chat-list::-webkit-scrollbar {
    width: 8px;
}

.chat-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 8px;
}

.chat-list::-webkit-scrollbar-track {
    background: transparent;
}

/* Pager */
.pager {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 8px 12px 12px;
    border-top: 1px solid var(--border);
}

.pager-btn {
    padding: 0 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #fff;
    cursor: pointer;
    font-size: 13px;
    height: 36px;
}

.pager-btn.square {
    width: 36px;
    height: 36px;
    padding: 0;
    display: grid;
    place-items: center;
    border-radius: 6px; /* kare görünüm */
    font-weight: 600;
}

.pager-btn.arrow {
    font-size: 18px;
}

.pager-btn.active {
    background: #1e72e8;
    color: #fff;
    border-color: var(--active);
}

.pager-btn.disabled {
    opacity: .45;
    cursor: not-allowed;
}

.chat-item {
    display: grid;
    grid-template-columns: 48px 1fr;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    cursor: pointer;
    align-items: center;
    transition: background .15s ease;
}

.chat-item:hover {
    background: #f1f5f9;
}

.chat-item.active {
    background: #1e72e8;
    color: #fff;
}

.chat-item.active .time,
.chat-item.active .text {
    color: #e8eef6;
}

.avatar,
.avatar.small {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    background: #e2e8f0;
    display: grid;
    place-items: center;
    font-weight: 700;
    color: #334155;
}

.avatar.small {
    width: 36px;
    height: 36px;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-fallback {
    font-size: 14px;
}

.meta {
    min-width: 0;
}

.top {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 8px;
}

.name {
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.time {
    color: var(--muted);
    font-size: 12px;
    white-space: nowrap;
}

.preview {
    display: flex;
    align-items: center;
    gap: 8px;
}

.preview .text {
    color: var(--muted);
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.badge {
    background: var(--blue);
    color: #fff;
    border-radius: 999px;
    font-size: 11px;
    padding: 2px 6px;
    line-height: 1;
}

/* Conversation */
.conversation {
    display: grid;
    grid-template-rows: auto 1fr auto;
    background: var(--panel);
    margin-right: 10px;
}

.conversation.hidden {
    display: none;
}

.conv-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    background: #fbfcfd;
}

.back-btn {
    border: none;
    background: transparent;
    font-size: 20px;
    cursor: pointer;
}

.peer {
    display: flex;
    align-items: center;
    gap: 12px;
}

.peer-meta h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
}

.peer-meta .sub {
    font-size: 12px;
    color: var(--muted);
}

.messages {
    max-height: 73vh;
    border-radius: 0 0 15px 15px;
    overflow-y: auto;
    padding: 24px 24px 12px;
    background: linear-gradient(to bottom, #f7f8fb 0%, #f1f4f9 60%, #ffffff 100%);
}

.message-row {
    display: flex;
    margin-bottom: 10px;
}

.message-row.mine {
    justify-content: flex-end;
}

.bubble {
    max-width: min(70%, 640px);
    background: #eef2f7;
    padding: 10px 14px;
    border-radius: 14px;
    position: relative;
}

.message-row.mine .bubble {
    background: #1e73e8;
    color: #fff;
}

.sender {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 4px;
}

.message-row.mine .sender {
    color: #cfe0ff;
}

.text {
    white-space: pre-wrap;
}

/* Attachments */
.att-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.att-item {
    border: 1px solid #e5e7eb;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    max-width: 160px;
}

.att-thumb {
    display: block;
    width: 160px;
    height: 100px;
    object-fit: cover;
}

.att-thumb-link {
    display: block;
}

.att-file {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    min-width: 160px;
    text-decoration: none;
    color: inherit;
    border: 1px solid #e5e7eb;
    background: #fff;
    border-radius: 10px;
}

.att-file:hover {
    background: #f8fafc;
}

.att-icon {
    font-size: 18px;
    color: white;
}

.att-meta {
    min-width: 0;
}

.att-name {
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.att-size {
    font-size: 11px;
    color: #6b7280;
}

.stamp {
    font-size: 11px;
    margin-top: 6px;
    color: #7b8794;
    text-align: right;
    opacity: .9;
}

.message-row.mine .stamp {
    color: #e5efff;
}

/* Typing dots */
.typing {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 6px 0 14px 6px;
}

.dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #94a3b8;
    animation: blink 1.2s infinite ease-in-out;
}

.dot:nth-child(2) {
    animation-delay: .15s;
}

.dot:nth-child(3) {
    animation-delay: .3s;
}

@keyframes blink {
    0%, 80%, 100% {
        opacity: .3;
    }
    40% {
        opacity: 1;
    }
}

/* Composer */
.composer {
    position: relative;
    border-top: 1px solid var(--border);
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
    padding: 8px;
}

.composer textarea {
    border-radius: 15px;
    flex: 1;
    resize: none;
    min-height: 44px;
    max-height: 140px;
    padding: 10px 12px;
    border: 1px solid var(--border);
    outline: none;
    background: #f9fafb;
    font-family: inherit;
}

.actions {
    display: flex;
    gap: 6px;
}

.icon-btn {
    border: 1px solid var(--border);
    background: #fff;
    height: 44px;
    width: 44px;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-btn:hover {
    color: #1e73e8;
    border-color: #1e73e8;
}

.attach-list {
    position: absolute;
    bottom: -40px;
    left: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.attach-chip {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 20px;
    padding: 4px 10px;
    border: 1px solid var(--border);
    font-size: 13px;
    white-space: nowrap;
}

.attach-chip .chip-name {
    margin-right: 6px;
}

.attach-chip .chip-remove {
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
}

.chat-app.has-attachments {
    padding-bottom: 72px;
}

.placeholder {
    display: grid;
    place-items: center;
    color: var(--muted);
    font-weight: 500;
}

/* Responsive */
@media (max-width: 991.98px) {
    .chat-app {
        grid-template-columns: 1fr;
    }
}
</style>
