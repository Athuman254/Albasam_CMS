<template>
   <div v-if="editor" class="container mt-2">
      <div class="control-group">
         <div class="button-group">
            <button @click.prevent="editor.chain().focus().toggleBold().run()" :disabled="!editor.can().chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }">
               Bold
            </button>
            <button @click.prevent="editor.chain().focus().toggleItalic().run()" :disabled="!editor.can().chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }">
               Italic
            </button>
            <button @click.prevent="editor.chain().focus().toggleStrike().run()" :disabled="!editor.can().chain().focus().toggleStrike().run()" :class="{ 'is-active': editor.isActive('strike') }">
               Strike
            </button>
            <button @click.prevent="editor.chain().focus().toggleCode().run()" :disabled="!editor.can().chain().focus().toggleCode().run()" :class="{ 'is-active': editor.isActive('code') }">
               Code
            </button>
            <button @click.prevent="editor.chain().focus().unsetAllMarks().run()">
               Clear marks
            </button>
            <button @click.prevent="editor.chain().focus().clearNodes().run()">
               Clear nodes
            </button>
            <button @click.prevent="editor.chain().focus().setParagraph().run()" :class="{ 'is-active': editor.isActive('paragraph') }">
               Paragraph
            </button>
            <button @click.prevent="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }">
               H1
            </button>
            <button @click.prevent="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }">
               H2
            </button>
            <button @click.prevent="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }">
               H3
            </button>
            <button @click.prevent="editor.chain().focus().toggleHeading({ level: 4 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 4 }) }">
               H4
            </button>
            <button @click.prevent="editor.chain().focus().toggleHeading({ level: 5 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 5 }) }">
               H5
            </button>
            <button @click.prevent="editor.chain().focus().toggleHeading({ level: 6 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 6 }) }">
               H6
            </button>
            <button @click.prevent="editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': editor.isActive('bulletList') }">
               Bullet list
            </button>
            <button @click.prevent="editor.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': editor.isActive('orderedList') }">
               Ordered list
            </button>
            <button @click.prevent="editor.chain().focus().toggleCodeBlock().run()" :class="{ 'is-active': editor.isActive('codeBlock') }">
               Code block
            </button>
            <button @click.prevent="editor.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': editor.isActive('blockquote') }">
               Blockquote
            </button>
            <button @click.prevent="editor.chain().focus().setHorizontalRule().run()">
               Horizontal rule
            </button>
            <button @click.prevent="editor.chain().focus().setHardBreak().run()">
               Hard break
            </button>
            <button @click.prevent="editor.chain().focus().undo().run()" :disabled="!editor.can().chain().focus().undo().run()">
               Undo
            </button>
            <button @click.prevent="editor.chain().focus().redo().run()" :disabled="!editor.can().chain().focus().redo().run()">
               Redo
            </button>
         </div>
      </div>
      <editor-content :editor="editor" />
   </div>
</template>

<script>
import BulletList from '@tiptap/extension-bullet-list'
import TextStyle from '@tiptap/extension-text-style'
import StarterKit from '@tiptap/starter-kit'
import { Editor, EditorContent } from '@tiptap/vue-3'

export default {
   components: {
      EditorContent,
   },
   props: {
      modelValue: {
         type: String,
         default: '',
      },
   },
   data() {
      return {
         editor: null,
         extensions: StarterKit,
      }
   },
   watch: {
      modelValue(newVal) {
         if (this.editor && newVal !== this.editor.getHTML()) {
            this.editor.commands.setContent(newVal)
         }
      },
   },
   mounted() {
      this.editor = new Editor({
         extensions: [
            TextStyle.configure({ types: [BulletList.name] }),
            StarterKit,
         ],
         content: this.modelValue,
         injectCSS: true,
         onUpdate: ({ editor }) => {
            this.$emit('update:modelValue', editor.getHTML())
         },
      })
   },
   beforeUnmount() {
      this.editor.destroy()
   },
}
</script>

<style scoped lang="scss">
#root {
   --white: #fff;
   --black: #2e2b29;
   --black-contrast: #110F0E;
   --gray-1: rgba(61, 37, 20, .05);
   --gray-2: rgba(61, 37, 20, .08);
   --gray-3: rgba(61, 37, 20, .12);
   --purple: #6A00F5;
   --purple-contrast: #5800cc;
   --purple-light: rgba(88, 5, 255, .05);
}
.container.editor-control {
   padding: 0 !important;
   border: 1px solid rgb(206.38, 209.46, 212.54) !important;
   border-radius: 10px;
}
.control-group {
   align-items: flex-start;
   background-color: #fff;
   display: flex;
   flex-direction: column;
   gap: 1rem;
   padding: .35rem;
   border-bottom: 1px solid rgb(206.38, 209.46, 212.54) !important;
   border-top-left-radius: 10px;
   border-top-right-radius: 10px;
}
.button-group {
   display: flex;
   flex-wrap: wrap;
   gap: .25rem;
}
.button-group > button {
   background: rgba(61, 37, 20, 0.08);
   border-radius: .5rem;
   border: none;
   color: var(--black);
   font-family: inherit;
   font-size: .875rem;
   font-weight: 500;
   line-height: 1.15;
   margin: 0;
   padding: .375rem .625rem;
   transition: all .2s cubic-bezier(.65,.05,.36,1);
}
.button-group > button.is-active {
   background: var(--bs-primary);
   color: #fff;
}
::v-deep(.tiptap) {
   margin: 0.8rem;
   min-height: 50px;
   
   :first-child {
      margin-top: 0;
   }
   ul, ol {
      padding: 0 1rem;
      margin: 1.25rem 1rem 1.25rem 0.4rem;
      
      li p {
         margin-top: 0.25em;
         margin-bottom: 0.25em;
      }
   }
   h1, h2, h3, h4, h5, h6 {
      line-height: 1.1;
      margin-top: 2.5rem;
      text-wrap: pretty;
   }
   h1, h2 {
      margin-top: 3.5rem;
      margin-bottom: 1.5rem;
   }
   h1 {
      font-size: 1.4rem;
   }
   h2 {
      font-size: 1.2rem;
   }
   h3 {
      font-size: 1.1rem;
   }
   h4, h5, h6 {
      font-size: 1rem;
   }
   blockquote {
      border-left: 3px solid var(--gray-3) !important;
      margin: 1.5rem 0 !important;
      padding-left: 1rem !important;
   }
   code {
      background-color: var(--purple-light);
      border-radius: 0.4rem;
      color: var(--black);
      font-size: 0.85rem;
      padding: 0.25em 0.3em;
   }
   pre {
      background: var(--black);
      border-radius: 0.5rem;
      color: var(--white);
      font-family: 'JetBrainsMono', monospace;
      margin: 1.5rem 0;
      padding: 0.75rem 1rem;
      
      code {
         background: none;
         color: inherit;
         font-size: 0.8rem;
         padding: 0;
      }
   }
   hr {
      border: none;
      border-top: 1px solid var(--gray-2);
      margin: 2rem 0;
   }
   &:focus {
      outline: none;
   }
}
button.is-active,
input.is-active,
select.is-active,
textarea.is-active {
   background:var(--purple);
   color:var(--white)
}
button.is-active:hover,
input.is-active:hover,
select.is-active:hover,
textarea.is-active:hover {
   background-color:var(--purple-contrast) !important;
   color:var(--white) !important;
}
button:not([disabled]),
select:not([disabled]) {
   cursor: pointer
}
</style>
