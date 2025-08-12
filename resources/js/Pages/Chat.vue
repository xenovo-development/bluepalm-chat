<template>
  <div class="chat-app">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ hidden: isNarrow && activeChat }">
      <div class="search-bar">
        <input v-model="query" type="text" placeholder="Search" @keydown.stop />
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
            <img v-if="chat.avatar" :src="chat.avatar" alt="" />
            <div v-else class="avatar-fallback">{{ initials(chat.name) }}</div>
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
    </aside>

    <!-- Conversation -->
    <main class="conversation" :class="{ hidden: isNarrow && !activeChat }">
      <div class="conv-header" v-if="activeChat">
        <button class="back-btn" v-if="isNarrow" @click="activeChat = null" aria-label="Back">←</button>
        <div class="peer">
          <div class="avatar small">
            <img v-if="activeChat.avatar" :src="activeChat.avatar" alt="" />
            <div v-else class="avatar-fallback">{{ initials(activeChat.name) }}</div>
          </div>
          <div class="peer-meta">
            <h2>{{ activeChat.name }}</h2>
            <div class="sub">Online</div>
          </div>
        </div>
      </div>

      <div v-if="activeChat" class="messages" ref="messagesEl">
        <div
          v-for="(m, i) in activeChat.messages"
          :key="m.id"
          class="message-row"
          :class="{ mine: m.from === 'me' }"
        >
          <div class="bubble">
            <div class="sender" v-if="showSender(i) && m.from !== 'me'">{{ activeChat.name }}</div>
            <div class="text">{{ m.text }}</div>
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
          <button class="icon-btn" title="Attach" aria-label="Attach">📎</button>
          <button class="icon-btn" title="Image" aria-label="Image">🖼️</button>
          <button class="send-btn" @click="send">Send</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: "ChatPage",
  data() {
    return {
      isNarrow: false,
      query: "",
      draft: "",
      chats: [
        {
          id: 1,
          name: "Ethan Carter",
          avatar: "",
          lastMessage: "Hey, how's it going?",
          lastMessageAt: new Date().setHours(10, 5),
          unreadCount: 0,
          typing: false,
          messages: [
            { id: "m1", from: "ethan", text: "Hey, how's it going?", at: new Date().setHours(10, 5) },
            { id: "m2", from: "me", text: "I'm doing well, thanks! How about you?", at: new Date().setHours(10, 6) },
            { id: "m3", from: "ethan", text: "I'm good too, just finishing up some work.", at: new Date().setHours(10, 8) },
            { id: "m4", from: "me", text: "Nice! Anything interesting?", at: new Date().setHours(10, 10) },
            { id: "m5", from: "ethan", text: "Yeah, a new project that's exciting.", at: new Date().setHours(10, 12) },
            { id: "m6", from: "me", text: "That sounds great! Tell me more.", at: new Date().setHours(10, 14) },
          ],
        },
        {
          id: 2,
          name: "Sophia Clark",
          lastMessage: "See you tomorrow!",
          lastMessageAt: new Date().setHours(9, 30),
          unreadCount: 2,
          typing: true,
          messages: [
            { id: "s1", from: "sophia", text: "Meeting at 10?", at: new Date().setHours(9, 20) },
            { id: "s2", from: "me", text: "Works for me.", at: new Date().setHours(9, 22) },
            { id: "s3", from: "sophia", text: "Great, see you tomorrow!", at: new Date().setHours(9, 30) },
          ],
        },
        {
          id: 3,
          name: "Liam Walker",
          lastMessage: "Sounds good!",
          lastMessageAt: new Date().setHours(8, 15),
          unreadCount: 0,
          typing: false,
          messages: [{ id: "l1", from: "liam", text: "Lunch later?", at: new Date().setHours(8, 15) }],
        },
      ],
      activeChat: null,
      fakeReplyTimer: null,
    };
  },
  computed: {
    filteredChats() {
      const q = this.query.trim().toLowerCase();
      return this.chats
        .filter(
          (c) =>
            c.name.toLowerCase().includes(q) ||
            (c.lastMessage && c.lastMessage.toLowerCase().includes(q))
        )
        .sort((a, b) => b.lastMessageAt - a.lastMessageAt);
    },
  },
  watch: {
    // Sohbet değişince alta kaydır ve okunmamışı sıfırla
    activeChat(newVal) {
      if (!newVal) return;
      this.$nextTick(this.scrollToBottom);
      newVal.unreadCount = 0;
    },
    // Aktif sohbetin mesajları değişince (gönderim veya cevap) alta kaydır
    "activeChat.messages": {
      handler() {
        this.$nextTick(this.scrollToBottom);
      },
      deep: true,
    },
  },
  mounted() {
    // ilk sohbeti aç
    this.activeChat = this.chats[0] || null;
    // responsive
    this.onResize();
    window.addEventListener("resize", this.onResize);
    // başlangıç kaydır
    this.$nextTick(this.scrollToBottom);
  },
  beforeUnmount() {
    window.removeEventListener("resize", this.onResize);
    if (this.fakeReplyTimer) clearTimeout(this.fakeReplyTimer);
  },
  methods: {
    openChat(chat) {
      this.activeChat = chat;
    },
    send() {
      const text = this.draft.trim();
      if (!text || !this.activeChat) return;
      const now = Date.now();
      this.activeChat.messages.push({
        id: this.randId(),
        from: "me",
        text,
        at: now,
      });
      this.activeChat.lastMessage = text;
      this.activeChat.lastMessageAt = now;
      this.draft = "";
      // sahte kısa cevap
      if (this.fakeReplyTimer) clearTimeout(this.fakeReplyTimer);
      this.fakeReplyTimer = setTimeout(() => {
        if (!this.activeChat) return;
        const replyNow = Date.now();
        this.activeChat.messages.push({
          id: this.randId(),
          from: "peer",
          text: "Got it.",
          at: replyNow,
        });
        this.activeChat.lastMessage = "Got it.";
        this.activeChat.lastMessageAt = replyNow;
      }, 700);
    },
    scrollToBottom() {
      const el = this.$refs.messagesEl;
      if (el) el.scrollTop = el.scrollHeight;
    },
    formatTime(ts) {
      const d = new Date(ts);
      return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
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
  },
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

.chat-app {
  display: grid;
  grid-template-columns: 320px 1fr;
  height: 100vh;
  background: var(--bg);
  overflow: hidden;
}

/* Sidebar */
.sidebar {
  border-right: 1px solid var(--border);
  background: var(--panel);
  display: flex;
  flex-direction: column;
}
.sidebar.hidden { display: none; }

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

.chat-list {
  list-style: none;
  margin: 0;
  padding: 8px;
  overflow-y: auto;
  flex: 1;
}
.chat-item {
  display: grid;
  grid-template-columns: 48px 1fr;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  cursor: pointer;
  align-items: center;
  transition: background 0.15s ease;
}
.chat-item:hover { background: #f1f5f9; }
.chat-item.active {
  background: var(--active);
  color: #fff;
}
.chat-item.active .time,
.chat-item.active .text { color: #e8eef6; }

.avatar,
.avatar.small {
  width: 48px; height: 48px;
  border-radius: 50%;
  overflow: hidden;
  background: #e2e8f0;
  display: grid;
  place-items: center;
  font-weight: 700;
  color: #334155;
}
.avatar.small { width: 36px; height: 36px; }
.avatar img { width: 100%; height: 100%; object-fit: cover; }
.avatar-fallback { font-size: 14px; }

.meta { min-width: 0; }
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
}
.conversation.hidden { display: none; }

.conv-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  background: #fbfcfd;
}
.back-btn {
  border: none; background: transparent; font-size: 20px; cursor: pointer;
}
.peer { display: flex; align-items: center; gap: 12px; }
.peer-meta h2 {
  margin: 0; font-size: 18px; font-weight: 700;
}
.peer-meta .sub { font-size: 12px; color: var(--muted); }

/* Messages */
.messages {
  overflow-y: auto;
  padding: 24px 24px 12px;
  background: linear-gradient(#f7f8fb, #f1f4f9);
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
.message-row.mine .sender { color: #cfe0ff; }
.text { white-space: pre-wrap; }
.stamp {
  font-size: 11px;
  margin-top: 6px;
  color: #7b8794;
  text-align: right;
  opacity: 0.9;
}
.message-row.mine .stamp { color: #e5efff; }

/* Typing indicator */
.typing {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin: 6px 0 14px 6px;
}
.dot {
  width: 6px; height: 6px; border-radius: 50%; background: #94a3b8;
  animation: blink 1.2s infinite ease-in-out;
}
.dot:nth-child(2) { animation-delay: .15s; }
.dot:nth-child(3) { animation-delay: .3s; }
@keyframes blink {
  0%, 80%, 100% { opacity: .3; }
  40% { opacity: 1; }
}

/* Composer */
.composer {
  border-top: 1px solid var(--border);
  padding: 12px;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
}
.composer textarea {
  width: 100%;
  resize: none;
  min-height: 44px;
  max-height: 140px;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid var(--border);
  outline: none;
  background: #f9fafb;
  font-family: inherit;
}
.actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.icon-btn {
  border: 1px solid var(--border);
  background: #fff;
  height: 44px; width: 44px;
  border-radius: 10px;
  cursor: pointer;
}
.send-btn {
  height: 44px;
  border: none;
  background: var(--blue);
  color: #fff;
  border-radius: 10px;
  padding: 0 18px;
  font-weight: 600;
  cursor: pointer;
}

/* Placeholder */
.placeholder {
  display: grid;
  place-items: center;
  color: var(--muted);
  font-weight: 500;
}

/* Responsive */
@media (max-width: 991.98px) {
  .chat-app { grid-template-columns: 1fr; }
}
</style>
