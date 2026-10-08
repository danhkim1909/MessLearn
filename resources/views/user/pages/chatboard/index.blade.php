@extends('user.layouts.app')
@section('title', 'MessLearn - Cửa sổ trò chuyện & Học tập')

@section('content')
<div class="flex-1 flex overflow-hidden h-[calc(100vh-3.5rem)]">
    {{-- Cột 1: Sidebar điều hướng hẹp --}}
    @include('user.pages.chatboard.partials.sidebar-nav')

    {{-- Cột 2: Danh sách cuộc trò chuyện --}}
    @include('user.pages.chatboard.partials.sidebar-conversations')

    <!-- CỘT 3: CỬA SỔ CHAT CHÍNH -->
    <main class="flex-1 bg-white dark:bg-slate-900 flex flex-col min-w-0 border-r border-slate-200 dark:border-slate-800 relative">
        @if(isset($activeConversation))
            {{-- Header Chat --}}
            @include('user.pages.chatboard.partials.chat.header')

            {{-- Nội dung Chat & Tin nhắn --}}
            @include('user.pages.chatboard.partials.chat.message-list')

            {{-- Khung nhập tin nhắn & Ghi âm & File đính kèm --}}
            @include('user.pages.chatboard.partials.chat.input-bar')
        @else
            {{-- Trạng thái chưa chọn cuộc trò chuyện --}}
            @include('user.pages.chatboard.partials.chat.empty-state')
        @endif
    </main>

    {{-- Cột 4: Không gian học tập --}}
    @if(isset($activeConversation))
        @include('user.pages.chatboard.partials.sidebar-learning')
    @endif
</div>

    <!-- CÁC POPUPS MODAL -->
    @include('user.pages.chatboard.partials.modals.modal-lightbox')
    @include('user.pages.chatboard.partials.modals.modal-create-quiz')
    @include('user.pages.chatboard.partials.modals.modal-mini-games')
    @include('user.pages.chatboard.partials.modals.modal-add-friend')
    @include('user.pages.chatboard.partials.modals.modal-create-group')
    @include('user.pages.chatboard.partials.modals.modal-take-quiz')
    @include('user.pages.chatboard.partials.modals.modal-quiz-leaderboard')
    @include('user.pages.chatboard.partials.modals.modal-create-event')
    @include('user.pages.chatboard.partials.modals.modal-document-viewer')
    @include('user.pages.chatboard.partials.modals.modal-forward-message')
    @include('user.pages.chatboard.partials.modals.modal-incoming-call')
    @include('user.pages.chatboard.partials.modals.modal-meeting-lobby')
    @include('user.pages.chatboard.partials.modals.modal-meeting-room')
    @include('user.pages.chatboard.partials.modals.modal-group-members')
    @include('user.pages.chatboard.partials.modals.modal-user-settings')

@endsection

@section('scripts')
<script src="https://unpkg.com/painterro@1.2.55/build/painterro.min.js"></script>

{{-- Scripts Modules --}}
@if(isset($activeConversation))
    @include('user.pages.chatboard.partials.scripts.voice-audio')
    @include('user.pages.chatboard.partials.scripts.image-canvas')
    @include('user.pages.chatboard.partials.scripts.mini-games')
    @include('user.pages.chatboard.partials.scripts.quiz')
@endif
@include('user.pages.chatboard.partials.scripts.meeting-webrtc')
@include('user.pages.chatboard.partials.scripts.chat-core')
@include('user.pages.chatboard.partials.scripts.user-settings')
@endsection
