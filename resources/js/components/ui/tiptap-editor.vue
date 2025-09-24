<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Placeholder from '@tiptap/extension-placeholder'
import TextAlign from '@tiptap/extension-text-align'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableHeader } from '@tiptap/extension-table-header'
import { TableCell } from '@tiptap/extension-table-cell'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { createLowlight } from 'lowlight'
import javascript from 'highlight.js/lib/languages/javascript'
import typescript from 'highlight.js/lib/languages/typescript'
import php from 'highlight.js/lib/languages/php'
import python from 'highlight.js/lib/languages/python'
import css from 'highlight.js/lib/languages/css'
import html from 'highlight.js/lib/languages/xml'
import json from 'highlight.js/lib/languages/json'
import bash from 'highlight.js/lib/languages/bash'
import { watch, onBeforeUnmount, onMounted, ref, nextTick } from 'vue'
import { Bold, Italic, Strikethrough, Underline as UnderlineIcon, Heading1, Heading2, Heading3, List, ListOrdered, Quote, Code2, Code, Eraser, Undo, Redo, AlignLeft, AlignCenter, AlignRight, AlignJustify, Minus, Link as LinkIcon, Unlink, ChevronDown, Type, Pilcrow } from 'lucide-vue-next'

// Create a lowlight instance with common languages
const lowlight = createLowlight()
lowlight.register('javascript', javascript)
lowlight.register('typescript', typescript)
lowlight.register('php', php)
lowlight.register('python', python)
lowlight.register('css', css)
lowlight.register('html', html)
lowlight.register('json', json)
lowlight.register('bash', bash)

interface Props {
  modelValue?: string
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Start writing...'
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

// Menu state
const showCodeBlockMenu = ref(false)
const showHeadingsMenu = ref(false)
const showAlignMenu = ref(false)
const showTextFormatMenu = ref(false)

// Sticky toolbar state
const toolbarRef = ref<HTMLElement>()
const editorContainerRef = ref<HTMLElement>()
const isSticky = ref(false)
const toolbarHeight = ref(0)
const editorMaxHeight = ref('75vh')

// Available programming languages
const codeLanguages = [
  { label: 'Plain Text', value: null },
  { label: 'JavaScript', value: 'javascript' },
  { label: 'TypeScript', value: 'typescript' },
  { label: 'PHP', value: 'php' },
  { label: 'Python', value: 'python' },
  { label: 'CSS', value: 'css' },
  { label: 'HTML', value: 'html' },
  { label: 'JSON', value: 'json' },
  { label: 'Bash/Shell', value: 'bash' },
]

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit.configure({
      // Disable the default codeBlock to use CodeBlockLowlight
      codeBlock: false,
      // History, Underline, Link — allready included in StarterKit
    }),
    CodeBlockLowlight.configure({
      lowlight,
      defaultLanguage: 'javascript',
    }),
    Placeholder.configure({
      placeholder: props.placeholder,
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    Image,
    Table.configure({
      resizable: true,
    }),
    TableRow,
    TableHeader,
    TableCell,
    TaskList,
    TaskItem.configure({
      nested: true,
    }),
  ],
  editorProps: {
    attributes: {
      class: 'tiptap-content focus:outline-none',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

// Toggle code block menu
const toggleCodeBlockMenu = () => {
  showCodeBlockMenu.value = !showCodeBlockMenu.value
  showHeadingsMenu.value = false
  showAlignMenu.value = false
  showTextFormatMenu.value = false
}

// Insert code block with specific language
const insertCodeBlock = (language: string | null) => {
  if (language) {
    editor.value?.chain().focus().setCodeBlock({ language }).run()
  } else {
    editor.value?.chain().focus().setCodeBlock().run()
  }
  showCodeBlockMenu.value = false
}

// Toggle headings menu
const toggleHeadingsMenu = () => {
  showHeadingsMenu.value = !showHeadingsMenu.value
  showCodeBlockMenu.value = false
  showAlignMenu.value = false
  showTextFormatMenu.value = false
}

// Toggle align menu
const toggleAlignMenu = () => {
  showAlignMenu.value = !showAlignMenu.value
  showCodeBlockMenu.value = false
  showHeadingsMenu.value = false
  showTextFormatMenu.value = false
}

// Set heading level or paragraph
const setHeading = (level: 1 | 2 | 3 | 'paragraph') => {
  if (level === 'paragraph') {
    editor.value?.chain().focus().setParagraph().run()
  } else {
    editor.value?.chain().focus().toggleHeading({ level }).run()
  }
  showHeadingsMenu.value = false
}

// Set text alignment
const setAlignment = (align: 'left' | 'center' | 'right' | 'justify') => {
  editor.value?.chain().focus().setTextAlign(align).run()
  showAlignMenu.value = false
}

// Toggle text format menu
const toggleTextFormatMenu = () => {
  showTextFormatMenu.value = !showTextFormatMenu.value
  showCodeBlockMenu.value = false
  showHeadingsMenu.value = false
  showAlignMenu.value = false
}

// Apply text format
const applyTextFormat = (format: 'underline' | 'strike' | 'code') => {
  switch (format) {
    case 'underline':
      editor.value?.chain().focus().toggleUnderline().run()
      break
    case 'strike':
      editor.value?.chain().focus().toggleStrike().run()
      break
    case 'code':
      editor.value?.chain().focus().toggleCode().run()
      break
  }
  showTextFormatMenu.value = false
}

// Close menus when clicking outside
const closeAllMenus = () => {
  showCodeBlockMenu.value = false
  showHeadingsMenu.value = false
  showAlignMenu.value = false
  showTextFormatMenu.value = false
}

// Focus editor when clicking on container
const focusEditor = () => {
  if (editor.value) {
    editor.value.chain().focus().run()
  }
}

// Link functionality
const addLink = () => {
  const url = prompt('Enter URL:')
  if (url) {
    editor.value?.chain().focus().setLink({ href: url }).run()
  }
}

const removeLink = () => {
  editor.value?.chain().focus().unsetLink().run()
}

// Insert horizontal rule
const insertHorizontalRule = () => {
  editor.value?.chain().focus().setHorizontalRule().run()
}

// Calculate dynamic editor height based on toolbar height
const calculateEditorHeight = () => {
  if (!toolbarRef.value) return
  
  const currentToolbarHeight = toolbarRef.value.offsetHeight
  const toolbarHeightInVh = (currentToolbarHeight / window.innerHeight) * 100
  
  // Базовая высота 90vh минус высота панели инструментов и небольшой отступ
  const maxHeight = 90 - toolbarHeightInVh - 3 // 2vh для отступов
  editorMaxHeight.value = `${maxHeight}vh`
}

// Sticky toolbar functionality
const handleScroll = () => {
  if (!toolbarRef.value || !editorContainerRef.value) return

  const toolbarRect = toolbarRef.value.getBoundingClientRect()
  const containerRect = editorContainerRef.value.getBoundingClientRect()
  
  // Check if toolbar should be sticky
  const shouldBeSticky = containerRect.top <= 0 && containerRect.bottom > toolbarHeight.value
  
  if (shouldBeSticky !== isSticky.value) {
    isSticky.value = shouldBeSticky
    
    if (shouldBeSticky) {
      // Используем позицию самой панели инструментов вместо контейнера
      toolbarRef.value.style.position = 'fixed'
      toolbarRef.value.style.top = '0'
      toolbarRef.value.style.left = `${toolbarRect.left}px`
      toolbarRef.value.style.width = `${toolbarRect.width}px`
      toolbarRef.value.style.zIndex = '1000'
      toolbarRef.value.style.borderRadius = '0'
      
      // Добавляем отступ сверху для контента редактора, чтобы компенсировать fixed позицию панели
      const editorContent = editorContainerRef.value.querySelector('.editor-content-container') as HTMLElement
      if (editorContent) {
        editorContent.style.paddingTop = `${toolbarHeight.value}px`
      }
    } else {
      toolbarRef.value.style.position = ''
      toolbarRef.value.style.top = ''
      toolbarRef.value.style.left = ''
      toolbarRef.value.style.width = ''
      toolbarRef.value.style.zIndex = ''
      toolbarRef.value.style.borderRadius = ''
      
      // Убираем отступ сверху, когда панель возвращается в обычный поток
      const editorContent = editorContainerRef.value.querySelector('.editor-content-container') as HTMLElement
      if (editorContent) {
        editorContent.style.paddingTop = ''
      }
    }
  }
}

// Handle window resize to recalculate editor height
const handleResize = () => {
  calculateEditorHeight()
}

// Add click outside listener and scroll listener
onMounted(() => {
  document.addEventListener('click', closeAllMenus)
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('resize', handleResize, { passive: true })
  
  nextTick(() => {
    if (toolbarRef.value) {
      toolbarHeight.value = toolbarRef.value.offsetHeight
      calculateEditorHeight() // Рассчитываем высоту при монтировании
    }
  })
})

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (editor.value && editor.value.getHTML() !== newValue) {
    editor.value.commands.setContent(newValue, { emitUpdate: false })
  }
})

// Watch for placeholder changes
watch(() => props.placeholder, (newPlaceholder) => {
  if (editor.value) {
    const placeholderExt = editor.value.extensionManager.extensions.find(ext => ext.name === 'placeholder')
    if (placeholderExt) {
      placeholderExt.options.placeholder = newPlaceholder
    }
  }
})

// Cleanup
onBeforeUnmount(() => {
  document.removeEventListener('click', closeAllMenus)
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('resize', handleResize)
  editor.value?.destroy()
})
</script>

<template>
  <div ref="editorContainerRef" class="tiptap-editor border border-border rounded-md">
    <!-- Toolbar -->
    <div 
      ref="toolbarRef"
      class="border-b border-border rounded-t-md p-2 bg-background flex flex-wrap gap-1 transition-shadow duration-500"
      :class="{ 'shadow-lg': isSticky }"
      >
      <!-- Undo/Redo buttons -->
      <button
        type="button"
        @click="editor?.chain().focus().undo().run()"
        :disabled="!editor?.can().undo()"
        class="p-2 rounded hover:bg-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        title="Undo"
      >
        <Undo :size="20" stroke-width="1.5" />
      </button>

      <button
        type="button"
        @click="editor?.chain().focus().redo().run()"
        :disabled="!editor?.can().redo()"
        class="p-2 rounded hover:bg-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        title="Redo"
      >
        <Redo :size="20" stroke-width="1.5" />
      </button>

      <div class="w-px h-6 bg-border mx-1"></div>

      <!-- Format buttons -->
      <button
        type="button"
        @click="editor?.chain().focus().toggleBold().run()"
        :class="{ 'bg-accent': editor?.isActive('bold') }"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Bold"
      >
        <Bold :size="20" stroke-width="1.5" />
      </button>

      <button
        type="button"
        @click="editor?.chain().focus().toggleItalic().run()"
        :class="{ 'bg-accent': editor?.isActive('italic') }"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Italic"
      >
        <Italic :size="20" stroke-width="1.5" />
      </button>

      <!-- Text Format Dropdown -->
      <div class="relative">
        <button
          type="button"
          @click.stop="toggleTextFormatMenu"
          :class="{ 'bg-accent': editor?.isActive('underline') || editor?.isActive('strike') || editor?.isActive('code') }"
          class="p-2 rounded hover:bg-accent transition-colors flex items-center gap-1"
          title="Text Format"
        >
          <UnderlineIcon v-if="editor?.isActive('underline')" :size="20" stroke-width="1.5" />
          <Strikethrough v-else-if="editor?.isActive('strike')" :size="20" stroke-width="1.5" />
          <Code v-else-if="editor?.isActive('code')" :size="20" stroke-width="1.5" />
          <UnderlineIcon v-else :size="20" stroke-width="1.5" />
          <ChevronDown :size="16" stroke-width="1.5" />
        </button>
        
        <!-- Text Format Dropdown Menu -->
        <div v-if="showTextFormatMenu" @click.stop class="absolute top-full left-0 mt-1 bg-background border border-border rounded-md shadow-lg z-10 min-w-[160px]">
          <button
            type="button"
            @click="applyTextFormat('underline')"
            :class="{ 'bg-accent': editor?.isActive('underline') }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <UnderlineIcon :size="18" stroke-width="1.5" />
            Подчеркнутый
          </button>
          <button
            type="button"
            @click="applyTextFormat('strike')"
            :class="{ 'bg-accent': editor?.isActive('strike') }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <Strikethrough :size="18" stroke-width="1.5" />
            Зачеркнутый
          </button>
          <button
            type="button"
            @click="applyTextFormat('code')"
            :class="{ 'bg-accent': editor?.isActive('code') }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <Code :size="18" stroke-width="1.5" />
            Моноширинный
          </button>
        </div>
      </div>

      <div class="w-px h-6 bg-border mx-1"></div>

      <!-- Headings Dropdown -->
      <div class="relative">
        <button
          type="button"
          @click.stop="toggleHeadingsMenu"
          :class="{ 'bg-accent': editor?.isActive('heading') }"
          class="p-2 rounded hover:bg-accent transition-colors flex items-center gap-1"
          title="Text Style"
        >
          <Heading1 v-if="editor?.isActive('heading', { level: 1 })"      :size="20" stroke-width="1.5" />
          <Heading2 v-else-if="editor?.isActive('heading', { level: 2 })" :size="20" stroke-width="1.5" />
          <Heading3 v-else-if="editor?.isActive('heading', { level: 3 })" :size="20" stroke-width="1.5" />
          <Pilcrow v-else :size="20" stroke-width="1.5" />
          <ChevronDown :size="16" stroke-width="1.5" />
        </button>
        
        <!-- Headings Dropdown Menu -->
        <div v-if="showHeadingsMenu" @click.stop class="absolute top-full left-0 mt-1 bg-background border border-border rounded-md shadow-lg z-10 min-w-[150px]">
          <button
            type="button"
            @click="setHeading('paragraph')"
            :class="{ 'bg-accent': !editor?.isActive('heading') }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <Pilcrow :size="18" stroke-width="1.5" />
            Обычный текст
          </button>
          <div class="w-full h-px bg-border my-1"></div>
          <button
            type="button"
            @click="setHeading(1)"
            :class="{ 'bg-accent': editor?.isActive('heading', { level: 1 }) }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <Heading1 :size="18" stroke-width="1.5" />
            Heading 1
          </button>
          <button
            type="button"
            @click="setHeading(2)"
            :class="{ 'bg-accent': editor?.isActive('heading', { level: 2 }) }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <Heading2 :size="18" stroke-width="1.5" />
            Heading 2
          </button>
          <button
            type="button"
            @click="setHeading(3)"
            :class="{ 'bg-accent': editor?.isActive('heading', { level: 3 }) }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <Heading3 :size="18" stroke-width="1.5" />
            Heading 3
          </button>
        </div>
      </div>

      <div class="w-px h-6 bg-border mx-1"></div>

      <!-- Text Alignment Dropdown -->
      <div class="relative">
        <button
          type="button"
          @click.stop="toggleAlignMenu"
          :class="{ 'bg-accent': editor?.isActive({ textAlign: 'center' }) || editor?.isActive({ textAlign: 'right' }) || editor?.isActive({ textAlign: 'justify' }) }"
          class="p-2 rounded hover:bg-accent transition-colors flex items-center gap-1"
          title="Text Alignment"
        >
          <AlignLeft v-if="!editor?.isActive({ textAlign: 'center' }) && !editor?.isActive({ textAlign: 'right' }) && !editor?.isActive({ textAlign: 'justify' })" :size="20" stroke-width="1.5" />
          <AlignCenter v-else-if="editor?.isActive({ textAlign: 'center' })" :size="20" stroke-width="1.5" />
          <AlignRight v-else-if="editor?.isActive({ textAlign: 'right' })" :size="20" stroke-width="1.5" />
          <AlignJustify v-else-if="editor?.isActive({ textAlign: 'justify' })" :size="20" stroke-width="1.5" />
          <ChevronDown :size="16" stroke-width="1.5" />
        </button>
        
        <!-- Alignment Dropdown Menu -->
        <div v-if="showAlignMenu" @click.stop class="absolute top-full left-0 mt-1 bg-background border border-border rounded-md shadow-lg z-10 min-w-[140px]">
          <button
            type="button"
            @click="setAlignment('left')"
            :class="{ 'bg-accent': !editor?.isActive({ textAlign: 'center' }) && !editor?.isActive({ textAlign: 'right' }) && !editor?.isActive({ textAlign: 'justify' }) }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <AlignLeft :size="18" stroke-width="1.5" />
            Align Left
          </button>
          <button
            type="button"
            @click="setAlignment('center')"
            :class="{ 'bg-accent': editor?.isActive({ textAlign: 'center' }) }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <AlignCenter :size="18" stroke-width="1.5" />
            Align Center
          </button>
          <button
            type="button"
            @click="setAlignment('right')"
            :class="{ 'bg-accent': editor?.isActive({ textAlign: 'right' }) }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <AlignRight :size="18" stroke-width="1.5" />
            Align Right
          </button>
          <button
            type="button"
            @click="setAlignment('justify')"
            :class="{ 'bg-accent': editor?.isActive({ textAlign: 'justify' }) }"
            class="w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors flex items-center gap-2"
          >
            <AlignJustify :size="18" stroke-width="1.5" />
            Justify
          </button>
        </div>
      </div>

      <div class="w-px h-6 bg-border mx-1"></div>

      <!-- List buttons -->
      <button
        type="button"
        @click="editor?.chain().focus().toggleBulletList().run()"
        :class="{ 'bg-accent': editor?.isActive('bulletList') }"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Bullet List"
      >
        <List :size="24" stroke-width="1" />
      </button>

      <button
        type="button"
        @click="editor?.chain().focus().toggleOrderedList().run()"
        :class="{ 'bg-accent': editor?.isActive('orderedList') }"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Ordered List"
      >
        <ListOrdered :size="24" stroke-width="1" />
      </button>

      <div class="w-px h-6 bg-border mx-1"></div>

      <!-- Quote and Code -->
      <button
        type="button"
        @click="editor?.chain().focus().toggleBlockquote().run()"
        :class="{ 'bg-accent': editor?.isActive('blockquote') }"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Quote"
      >
        <Quote :size="20" stroke-width="1" />
      </button>

      <!-- Code Block with Language Selection -->
      <div class="relative">
        <button
          type="button"
          @click.stop="toggleCodeBlockMenu"
          :class="{ 'bg-accent': editor?.isActive('codeBlock') }"
          class="p-2 rounded hover:bg-accent transition-colors flex items-center gap-1"
          title="Code Block"
        >
          <Code2 :size="24" stroke-width="1.5" />
          <ChevronDown :size="16" stroke-width="1.5" />
        </button>
        
        <!-- Language Dropdown -->
        <div v-if="showCodeBlockMenu" @click.stop class="absolute top-full left-0 mt-1 bg-background border border-border rounded-md shadow-lg z-10 min-w-[160px]">
          <button
            v-for="language in codeLanguages"
            :key="language.value || 'plain'"
            type="button"
            @click="insertCodeBlock(language.value)"
            class="block w-full text-left px-3 py-2 text-sm hover:bg-accent transition-colors"
          >
            {{ language.label }}
          </button>
        </div>
      </div>

      <div class="w-px h-6 bg-border mx-1"></div>

      <!-- Link buttons -->
      <button
        type="button"
        @click="addLink"
        :class="{ 'bg-accent': editor?.isActive('link') }"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Add Link"
      >
        <LinkIcon :size="20" stroke-width="1.5" />
      </button>

      <button
        type="button"
        @click="removeLink"
        :disabled="!editor?.isActive('link')"
        class="p-2 rounded hover:bg-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        title="Remove Link"
      >
        <Unlink :size="20" stroke-width="1.5" />
      </button>

      <!-- Horizontal Rule -->
      <button
        type="button"
        @click="insertHorizontalRule"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Insert Horizontal Rule"
      >
        <Minus :size="20" stroke-width="1.5" />
      </button>

      <div class="w-px h-6 bg-border mx-1"></div>

      <!-- Clear Formatting -->
      <button
        type="button"
        @click="editor?.chain().focus().clearNodes().unsetAllMarks().run()"
        class="p-2 rounded hover:bg-accent transition-colors"
        title="Clear Formatting"
      >
        <Eraser :size="20" stroke-width="1.5" />
      </button>
    </div>

    <!-- Editor Container with scrolling -->
    <div class="relative editor-content-container">
      <EditorContent 
        :editor="editor" 
        @click="focusEditor"
        class="rounded-b-md min-h-[20vh] overflow-y-auto p-4 max-w-none focus-within:outline-none cursor-text tiptap-scrollable"
        :style="{ maxHeight: editorMaxHeight }"
      />
    </div>
  </div>
</template>
<!-- Стили для Tiptap вынесены в /resources/css/tiptap.css -->
