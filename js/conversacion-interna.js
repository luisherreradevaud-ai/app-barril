/**
 * ConversacionInterna - Componente de Conversaciones Internas
 * Versión: 1.0.0
 *
 * Uso:
 *   ConversacionInterna.render('mi-contenedor', 'tarea', '123', { height: '500px', compact: true });
 *
 * Para destruir:
 *   ConversacionInterna.destroy(convId);
 */

(function() {
  'use strict';

  // Inyectar templates HTML si no existen
  if (!document.getElementById('conversacion-comentario-template')) {
    var templatesHTML = `
      <!-- Template para un comentario -->
      <template id="conversacion-comentario-template">
        <div class="conversacion-comentario" data-comentario-id="">
          <div class="d-flex align-items-start">
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <p class="mb-0"><strong class="conversacion-comentario-autor"></strong></p>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <small class="text-muted conversacion-comentario-fecha"></small>
                  <div class="dropdown position-relative">
                    <button class="btn btn-sm btn-link text-muted conversacion-comentario-menu p-0" type="button" data-bs-toggle="dropdown" data-bs-display="static">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item conversacion-btn-eliminar-comentario" href="#"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Contenido del comentario -->
              <div class="conversacion-comentario-contenido mb-2"></div>

              <!-- Archivos adjuntos -->
              <div class="conversacion-comentario-archivos mb-2"></div>

              <!-- Timestamp y Acciones -->
              <div class="mt-1">
                <button type="button" class="btn btn-sm btn-danger conversacion-btn-like">
                  <i class="bi bi-heart"></i>
                  <span class="conversacion-likes-count ms-1"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Template para archivo adjunto -->
      <template id="conversacion-archivo-template">
        <a href="" target="_blank" class="conversacion-archivo d-inline-flex align-items-center p-2 border rounded me-2 mb-2 text-decoration-none">
          <i class="bi bi-file-earmark me-2"></i>
          <div>
            <div class="conversacion-archivo-nombre small"></div>
            <div class="conversacion-archivo-tamano text-muted" style="font-size: 0.75rem;"></div>
          </div>
        </a>
      </template>
    `;

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = templatesHTML;
    document.body.appendChild(tempDiv);
  }

  // Inyectar estilos CSS si no existen
  if (!document.getElementById('conversacion-interna-styles')) {
    var css = `
      /* Estilos del Componente de Conversación Interna */
      .conversacion-interna-container {
        background: transparent;
        padding: 0;
      }

      .conversacion-header {
        border-bottom: 1px solid var(--bs-border-color);
        padding-bottom: 15px;
        margin-bottom: 20px;
      }

      .conversacion-comentario {
        padding: 20px 0;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .conversacion-comentario:last-child {
        border-bottom: none;
      }

      .conversacion-comentario-contenido {
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.6;
      }

      .conversacion-mention {
        background-color: #e3f2fd;
        color: #1976d2;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 500;
      }

      .conversacion-archivo {
        transition: all 0.2s;
      }

      .conversacion-archivo:hover {
        background-color: var(--bs-light);
        border-color: var(--bs-primary) !important;
      }

      .conversacion-comentario-avatar img {
        object-fit: cover;
      }

      .conversacion-archivos-preview-item {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        background: var(--bs-light);
        border-radius: 4px;
        margin-right: 8px;
        margin-bottom: 8px;
      }

      .conversacion-menciones-lista .badge {
        margin-right: 5px;
        margin-bottom: 5px;
      }

      .conversacion-btn-like {
        border: none;
        background: transparent;
        color: var(--bs-danger);
      }

      .conversacion-btn-like:hover {
        background: var(--bs-danger);
        color: white;
      }

      .conversacion-btn-like.liked {
        background: var(--bs-danger);
        color: white;
      }

      .conversacion-btn-like.liked i.bi-heart:before {
        content: "\\f415"; /* bi-heart-fill */
      }

      .conversacion-postbox {
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 0.375rem;
      }

      /* Dropzone */
      .conversacion-dropzone .dropzone-area {
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        border-top: none !important;
        border-bottom-right-radius: 0.375rem !important;
        border-bottom-left-radius: 0.375rem !important;
      }

      .conversacion-dropzone .dropzone-area:hover {
        border-color: var(--bs-primary) !important;
        background: var(--bs-primary-bg-subtle);
      }

      .conversacion-dropzone .dropzone-area.dragover {
        border-color: var(--bs-primary) !important;
        background: var(--bs-primary-bg-subtle);
      }

      .conversacion-archivos-preview .file-item {
        padding: 0.5rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .conversacion-archivos-preview .file-item:last-child {
        border-bottom: none;
      }

      .conversacion-archivos-preview .file-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
      }
    `;

    var style = document.createElement('style');
    style.id = 'conversacion-interna-styles';
    style.textContent = css;
    document.head.appendChild(style);
  }
})();

/**
 * ConversacionInterna - Sistema de conversaciones internas
 */
var ConversacionInterna = (function() {
  'use strict';

  var instances = {};

  var config = {
    maxFiles: 5,
    maxFileSize: 20 * 1024 * 1024,
    refreshInterval: 60000,
    baseUrl: '/ajax'
  };

  function Conversation(containerId) {
    this.containerId = containerId;
    this.$container = $('#' + containerId);
    this.viewName = this.$container.data('view-name');
    this.entityId = this.$container.data('entity-id');
    this.conversationData = null;
    this.usuarios = [];
    this.refreshTimer = null;

    console.log('[Conversation Constructor] containerId:', containerId, 'viewName:', this.viewName, 'entityId:', this.entityId);

    this.init();
  }

  Conversation.prototype.init = function() {
    this.loadUsuarios();
    this.loadConversation();
    this.setupEventListeners();
    this.setupAutoRefresh();
  };

  Conversation.prototype.loadUsuarios = function() {
    var self = this;
    $.ajax({
      url: config.baseUrl + '/ajax_getSearch.php',
      method: 'GET',
      data: { type: 'usuarios', limit: 1000 },
      success: function(response) {
        if(response && Array.isArray(response)) {
          self.usuarios = response;
        }
      },
      error: function(error) {
        console.error('Error cargando usuarios:', error);
      }
    });
  };

  Conversation.prototype.loadConversation = function(preserveScroll) {
    var self = this;
    console.log('[loadConversation] Iniciando carga... preserveScroll:', preserveScroll);
    console.log('[loadConversation] viewName:', self.viewName, 'entityId:', self.entityId);

    // Solo mostrar loading en la primera carga (cuando conversationData es null)
    var isFirstLoad = (self.conversationData === null);

    if(isFirstLoad) {
      self.showLoading();
    }

    $.ajax({
      url: config.baseUrl + '/ajax_getConversacion.php',
      method: 'GET',
      data: {
        nombre_vista: self.viewName,
        id_entidad: self.entityId
      },
      dataType: 'json',
      success: function(response) {
        console.log('[loadConversation] Respuesta recibida:', response);
        if(response.status === 'OK' && response.data) {
          self.conversationData = response.data;
          console.log('[loadConversation] Comentarios:', response.data.comentarios ? response.data.comentarios.length : 0);
          self.renderConversation(preserveScroll);
        } else {
          console.error('[loadConversation] Error en respuesta:', response);
          self.showError(response.mensaje || 'Error al cargar la conversación');
        }
      },
      error: function(xhr, status, error) {
        console.error('[loadConversation] Error AJAX:', error);
        console.error('[loadConversation] XHR:', xhr);
        console.error('[loadConversation] Response text:', xhr.responseText);
        self.showError('Error de conexión al cargar la conversación');
      },
      complete: function() {
        // Solo ocultar loading si se mostró
        if(isFirstLoad) {
          self.hideLoading();
        }
      }
    });
  };

  Conversation.prototype.renderConversation = function(preserveScroll) {
    var self = this;
    var $comentariosContainer = self.$container.find('.conversacion-comentarios');
    var comentarios = self.conversationData.comentarios || [];

    console.log('[renderConversation] Renderizando', comentarios.length, 'comentarios');

    var scrollPosition = 0;
    if(preserveScroll) {
      scrollPosition = $(window).scrollTop();
      console.log('[renderConversation] Guardando scroll position:', scrollPosition);
    }

    $comentariosContainer.empty();

    if(comentarios.length === 0) {
      console.log('[renderConversation] No hay comentarios, mostrando mensaje');
      self.$container.find('.conversacion-sin-comentarios').show();
    } else {
      self.$container.find('.conversacion-sin-comentarios').hide();
      comentarios.forEach(function(comentario, index) {
        console.log('[renderConversation] Renderizando comentario', index + 1, ':', comentario.id);
        var $comentario = self.renderComentario(comentario);
        $comentariosContainer.append($comentario);
      });
    }

    if(preserveScroll && scrollPosition > 0) {
      console.log('[renderConversation] Restaurando scroll a:', scrollPosition);
      requestAnimationFrame(function() {
        window.scrollTo(0, scrollPosition);
      });
    }
  };

  Conversation.prototype.renderComentario = function(comentario) {
    var self = this;

    // Clonar correctamente desde el template HTML5
    var templateElement = document.getElementById('conversacion-comentario-template');
    if(!templateElement) {
      console.error('[renderComentario] Template no encontrado: conversacion-comentario-template');
      return $('<div>Error: Template no encontrado</div>');
    }

    var clonedContent = templateElement.content.cloneNode(true);

    // Envolver en un div temporal para poder manipular con jQuery
    var $wrapper = $('<div>').append(clonedContent);
    var $template = $wrapper.find('.conversacion-comentario').first();

    if($template.length === 0) {
      console.error('[renderComentario] No se encontró .conversacion-comentario en el template');
      console.log('[renderComentario] Contenido del wrapper:', $wrapper.html());
      return $('<div>Error: Template mal formado</div>');
    }

    console.log('[renderComentario] Template clonado OK para comentario:', comentario.id);

    $template.attr('data-comentario-id', comentario.id);

    // Eliminar cualquier imagen que pueda existir
    $template.find('img').remove();

    $template.find('.conversacion-comentario-autor').text(comentario.nombre_autor || 'Usuario');
    $template.find('.conversacion-comentario-fecha').text(self.formatFecha(comentario.fecha_creacion));

    var contenidoHTML = self.procesarMenciones(comentario.contenido);
    $template.find('.conversacion-comentario-contenido').html(contenidoHTML);

    if(comentario.archivos && comentario.archivos.length > 0) {
      var $archivosContainer = $template.find('.conversacion-comentario-archivos');
      $archivosContainer.empty();
      comentario.archivos.forEach(function(archivo) {
        var $archivo = self.renderArchivo(archivo);
        $archivosContainer.append($archivo);
      });
    } else {
      $template.find('.conversacion-comentario-archivos').hide();
    }

    if(comentario.tags && comentario.tags.length > 0) {
      var $tagsContainer = $template.find('.conversacion-comentario-tags');
      $tagsContainer.empty();
      comentario.tags.forEach(function(tag) {
        var $badge = $('<span class="badge bg-info me-1">@' + tag.nombre_usuario + '</span>');
        $tagsContainer.append($badge);
      });
    } else {
      $template.find('.conversacion-comentario-tags').hide();
    }

    var likes = comentario.likes_info || [];
    var likesCount = likes.length;
    var currentUserId = window.currentUserId || '';
    var hasLiked = likes.some(function(like) { return like.id === currentUserId; });

    $template.find('.conversacion-likes-count').text(likesCount);

    if(hasLiked) {
      $template.find('.conversacion-btn-like').addClass('liked');
    }

    if(likesCount > 0) {
      var nombresLikes = likes.slice(0, 3).map(function(like) { return like.nombre; }).join(', ');
      if(likesCount > 3) {
        nombresLikes += ' y ' + (likesCount - 3) + ' más';
      }
      $template.find('.conversacion-likes-usuarios').text(nombresLikes);
    }

    if(comentario.id_autor !== currentUserId) {
      $template.find('.conversacion-comentario-menu').closest('.dropdown').hide();
    }

    return $template;
  };

  Conversation.prototype.renderArchivo = function(archivo) {
    var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
    var fileExtension = archivo.nombre.split('.').pop().toLowerCase();
    var isImage = imageExtensions.includes(fileExtension);

    // Crear contenedor del archivo
    var $fileItem = $('<div>')
      .addClass('d-flex align-items-center gap-2 p-2 border rounded mb-2')
      .css({
        'background': 'var(--bs-light)',
        'cursor': 'pointer'
      })
      .on('click', function() {
        window.open(archivo.ruta_archivo, '_blank');
      });

    if(isImage) {
      // Mostrar thumbnail para imágenes
      var $thumbnail = $('<img>')
        .attr('src', archivo.ruta_archivo)
        .attr('alt', archivo.nombre)
        .addClass('file-thumbnail')
        .css({
          'width': '50px',
          'height': '50px',
          'object-fit': 'cover',
          'border-radius': '4px'
        });
      $fileItem.append($thumbnail);
    } else {
      // Mostrar icono para otros archivos
      $fileItem.append($('<i>').addClass('bi bi-file-earmark'));
    }

    // Nombre y tamaño del archivo
    var $fileDetails = $('<div>').addClass('flex-grow-1');

    var metadata = typeof archivo.metadata === 'string' ? JSON.parse(archivo.metadata) : archivo.metadata;
    var size = metadata && metadata.size ? this.formatFileSize(metadata.size) : '';

    $fileDetails.append(
      $('<div>').addClass('small fw-bold').text(archivo.nombre),
      size ? $('<div>').addClass('text-muted').css('font-size', '0.75rem').text(size) : null
    );

    $fileItem.append($fileDetails);

    return $fileItem;
  };

  Conversation.prototype.procesarMenciones = function(texto) {
    if(!texto) return '';
    texto = $('<div>').text(texto).html();
    var regex = /@(?:\[(.+?)\]\(user:([^)]+)\)|([^{]+?)\{([^}]+)\})/g;
    texto = texto.replace(regex, function(match, nombre1, id1, nombre2, id2) {
      var nombre = nombre1 || nombre2;
      var id = id1 || id2;
      return '<span class="conversacion-mention" data-user-id="' + id + '">@' + nombre + '</span>';
    });
    texto = texto.replace(/\n/g, '<br>');
    return texto;
  };

  Conversation.prototype.setupEventListeners = function() {
    var self = this;

    self.$container.find('.conversacion-form').on('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    });

    self.$container.find('.conversacion-btn-enviar').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      self.guardarComentario();
      return false;
    });

    // Botón de adjuntar archivos
    self.$container.find('.conversacion-btn-attach').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log('[setupEventListeners] Click en botón adjuntar');
      self.$container.find('.conversacion-input-archivos').click();
      return false;
    });

    // Dropzone: Click para seleccionar archivos (si existe)
    self.$container.find('.dropzone-area').on('click', function() {
      console.log('[setupEventListeners] Click en dropzone');
      self.$container.find('.conversacion-input-archivos').click();
    });

    // Dropzone: Drag and drop
    var $dropzone = self.$container.find('.dropzone-area');

    $dropzone.on('dragover', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).addClass('dragover');
    });

    $dropzone.on('dragleave', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('dragover');
    });

    $dropzone.on('drop', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('dragover');

      var files = e.originalEvent.dataTransfer.files;
      console.log('[setupEventListeners] Archivos arrastrados:', files.length);

      // Asignar archivos al input
      self.$container.find('.conversacion-input-archivos')[0].files = files;
      self.previewArchivos(files);
    });

    self.$container.find('.conversacion-input-archivos').on('change', function() {
      console.log('[setupEventListeners] Archivos seleccionados:', this.files.length);
      self.previewArchivos(this.files);
    });

    self.$container.on('click', '.conversacion-btn-like', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $comentario = $(this).closest('.conversacion-comentario');
      var comentarioId = $comentario.data('comentario-id');
      self.toggleLike(comentarioId);
      return false;
    });

    self.$container.on('click', '.conversacion-btn-eliminar-comentario', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $comentario = $(this).closest('.conversacion-comentario');
      var comentarioId = $comentario.data('comentario-id');
      if(confirm('¿Estás seguro de eliminar este comentario?')) {
        self.eliminarComentario(comentarioId);
      }
      return false;
    });

    self.setupMentionAutocomplete();
  };

  Conversation.prototype.setupMentionAutocomplete = function() {
    var self = this;
    var $textarea = self.$container.find('.conversacion-input-contenido');
    $textarea.on('keyup', function(e) {
      var text = $(this).val();
      var cursorPos = this.selectionStart;
      var textBeforeCursor = text.substring(0, cursorPos);
      var atMatch = textBeforeCursor.match(/@(\w*)$/);
      if(atMatch) {
        var query = atMatch[1].toLowerCase();
        var suggestions = self.usuarios.filter(function(user) {
          return user.nombre && user.nombre.toLowerCase().indexOf(query) !== -1;
        }).slice(0, 5);
      }
    });
  };

  Conversation.prototype.guardarComentario = function() {
    var self = this;
    var $form = self.$container.find('.conversacion-form');
    var contenido = self.$container.find('.conversacion-input-contenido').val().trim();
    var archivos = self.$container.find('.conversacion-input-archivos')[0].files;

    if(!contenido) {
      alert('Por favor escribe un comentario');
      return;
    }

    if(archivos.length > config.maxFiles) {
      alert('Máximo ' + config.maxFiles + ' archivos permitidos');
      return;
    }

    for(var i = 0; i < archivos.length; i++) {
      if(archivos[i].size > config.maxFileSize) {
        alert('El archivo "' + archivos[i].name + '" excede el tamaño máximo de 20MB');
        return;
      }
    }

    var tags = self.extractMentions(contenido);

    var formData = new FormData();
    formData.append('id_conversacion', self.conversationData.conversacion.id);
    formData.append('contenido', contenido);
    formData.append('estado', 'activo');
    formData.append('tags', JSON.stringify(tags));

    for(var i = 0; i < archivos.length; i++) {
      formData.append('archivo_' + i, archivos[i]);
      formData.append('archivo_' + i + '_meta', JSON.stringify({
        name: archivos[i].name,
        description: '',
        status: 'activo'
      }));
    }

    var $btnEnviar = self.$container.find('.conversacion-btn-enviar');
    $btnEnviar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Enviando...');

    $.ajax({
      url: config.baseUrl + '/ajax_guardarComentarioConArchivos.php',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        console.log('[guardarComentario] Respuesta del servidor:', response);
        if(response.status === 'OK') {
          console.log('[guardarComentario] Comentario guardado exitosamente, ID:', response.comentario_id);
          $form[0].reset();

          // Limpiar preview de archivos
          var $preview = self.$container.find('.conversacion-archivos-preview');
          $preview.hide();
          $preview.find('#files-list').empty();

          // Resetear input de archivos
          var $fileInput = self.$container.find('.conversacion-input-archivos');
          $fileInput.val('');

          console.log('[guardarComentario] Recargando conversación con preserveScroll=true');
          self.loadConversation(true);
        } else {
          console.error('[guardarComentario] Error en respuesta:', response);
          alert('Error al guardar: ' + (response.mensaje || 'Error desconocido'));
        }
      },
      error: function(xhr, status, error) {
        console.error('[guardarComentario] Error AJAX:', error);
        console.error('[guardarComentario] Response text:', xhr.responseText);
        alert('Error de conexión al guardar el comentario');
      },
      complete: function() {
        $btnEnviar.prop('disabled', false).text('Publicar');
      }
    });
  };

  Conversation.prototype.extractMentions = function(texto) {
    var mentions = [];
    var regex = /@(?:\[.+?\]\(user:([^)]+)\)|[^{]+?\{([^}]+)\})/g;
    var match;
    while((match = regex.exec(texto)) !== null) {
      var userId = match[1] || match[2];
      if(userId && mentions.indexOf(userId) === -1) {
        mentions.push(userId);
      }
    }
    return mentions;
  };

  Conversation.prototype.previewArchivos = function(files) {
    var self = this;
    var $preview = self.$container.find('.conversacion-archivos-preview');
    var $filesList = self.$container.find('#files-list');
    var $filesCount = self.$container.find('#files-count');

    console.log('[previewArchivos] Mostrando preview de', files.length, 'archivos');

    $filesList.empty();

    if(files.length === 0) {
      $preview.hide();
      return;
    }

    $preview.show();
    $filesCount.text(files.length);

    var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

    for(var i = 0; i < files.length; i++) {
      var file = files[i];
      var fileExtension = file.name.split('.').pop().toLowerCase();
      var isImage = imageExtensions.includes(fileExtension);

      var $fileItem = $('<div>')
        .addClass('file-item d-flex align-items-center justify-content-between');

      var $fileInfo = $('<div>').addClass('d-flex align-items-center gap-2');

      if(isImage) {
        // Crear thumbnail para imágenes
        var reader = new FileReader();
        reader.onload = (function(img) {
          return function(e) {
            img.attr('src', e.target.result);
          };
        })($('<img>')
          .addClass('file-thumbnail')
          .appendTo($fileInfo));
        reader.readAsDataURL(file);
      } else {
        // Icono para otros archivos
        $fileInfo.append($('<i>').addClass('bi bi-file-earmark'));
      }

      // Nombre y tamaño del archivo
      var $fileDetails = $('<div>').addClass('flex-grow-1');
      $fileDetails.append(
        $('<div>').addClass('small').text(file.name),
        $('<div>').addClass('text-muted').css('font-size', '0.75rem').text(self.formatFileSize(file.size))
      );

      $fileInfo.append($fileDetails);

      // Botón de eliminar
      var $removeBtn = $('<button>')
        .addClass('btn btn-sm text-danger')
        .attr('type', 'button')
        .attr('data-file-index', i)
        .html('<i class="bi bi-trash"></i>')
        .on('click', function() {
          var index = parseInt($(this).attr('data-file-index'));
          self.removeFileFromInput(index);
        });

      $fileItem.append($fileInfo, $removeBtn);
      $filesList.append($fileItem);
    }
  };

  Conversation.prototype.removeFileFromInput = function(index) {
    var self = this;
    var input = self.$container.find('.conversacion-input-archivos')[0];
    var dt = new DataTransfer();

    for(var i = 0; i < input.files.length; i++) {
      if(i !== index) {
        dt.items.add(input.files[i]);
      }
    }

    input.files = dt.files;
    self.previewArchivos(input.files);

    console.log('[removeFileFromInput] Archivo removido. Archivos restantes:', input.files.length);
  };

  Conversation.prototype.toggleLike = function(comentarioId) {
    var self = this;
    $.ajax({
      url: config.baseUrl + '/ajax_actualizarLikesComentario.php',
      method: 'POST',
      data: {
        id_comentario: comentarioId,
        accion: 'toggle'
      },
      dataType: 'json',
      success: function(response) {
        if(response.status === 'OK') {
          var $comentario = self.$container.find('[data-comentario-id="' + comentarioId + '"]');
          var $btnLike = $comentario.find('.conversacion-btn-like');
          var $count = $comentario.find('.conversacion-likes-count');
          $btnLike.toggleClass('liked');
          $count.text(response.total_likes || 0);
          setTimeout(function() {
            self.loadConversation(true);
          }, 500);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error al actualizar like:', error);
      }
    });
  };

  Conversation.prototype.eliminarComentario = function(comentarioId) {
    var self = this;
    $.ajax({
      url: config.baseUrl + '/ajax_eliminarComentario.php',
      method: 'POST',
      data: {
        id_comentario: comentarioId
      },
      dataType: 'json',
      success: function(response) {
        if(response.status === 'OK') {
          self.loadConversation(true);
        } else {
          alert('Error al eliminar: ' + (response.mensaje || 'Error desconocido'));
        }
      },
      error: function(xhr, status, error) {
        console.error('Error eliminando comentario:', error);
        alert('Error de conexión al eliminar el comentario');
      }
    });
  };

  Conversation.prototype.setupAutoRefresh = function() {
    var self = this;
    self.refreshTimer = setInterval(function() {
      self.loadConversation(true);
    }, config.refreshInterval);
  };

  Conversation.prototype.destroy = function() {
    if(this.refreshTimer) {
      clearInterval(this.refreshTimer);
    }
  };

  Conversation.prototype.showLoading = function() {
    this.$container.find('.conversacion-loading').show();
  };

  Conversation.prototype.hideLoading = function() {
    this.$container.find('.conversacion-loading').hide();
  };

  Conversation.prototype.showError = function(message) {
    console.error(message);
  };

  Conversation.prototype.formatFecha = function(fecha) {
    if(!fecha) return '';
    var date = new Date(fecha);
    var now = new Date();
    var diff = now - date;
    var seconds = Math.floor(diff / 1000);
    var minutes = Math.floor(seconds / 60);
    var hours = Math.floor(minutes / 60);
    var days = Math.floor(hours / 24);
    if(days > 7) {
      return date.toLocaleDateString('es-CL');
    } else if(days > 0) {
      return 'Hace ' + days + ' día' + (days > 1 ? 's' : '');
    } else if(hours > 0) {
      return 'Hace ' + hours + ' hora' + (hours > 1 ? 's' : '');
    } else if(minutes > 0) {
      return 'Hace ' + minutes + ' minuto' + (minutes > 1 ? 's' : '');
    } else {
      return 'Hace unos segundos';
    }
  };

  Conversation.prototype.formatFileSize = function(bytes) {
    if(bytes < 1024) return bytes + ' B';
    if(bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
  };

  Conversation.prototype.getFileIcon = function(mimetype) {
    if(!mimetype) return 'file';
    if(mimetype.indexOf('image/') === 0) return 'file-image';
    if(mimetype.indexOf('video/') === 0) return 'file-video';
    if(mimetype.indexOf('audio/') === 0) return 'file-audio';
    if(mimetype.indexOf('application/pdf') === 0) return 'file-text';
    if(mimetype.indexOf('application/zip') !== -1 || mimetype.indexOf('application/x-rar') !== -1) return 'file-archive';
    if(mimetype.indexOf('application/msword') !== -1 || mimetype.indexOf('application/vnd.openxmlformats') !== -1) return 'file-text';
    return 'file';
  };

  return {
    init: function(containerId) {
      if(!instances[containerId]) {
        instances[containerId] = new Conversation(containerId);
      }
      return instances[containerId];
    },
    getInstance: function(containerId) {
      return instances[containerId];
    },
    destroy: function(convId) {
      if(instances[convId]) {
        instances[convId].destroy();
        delete instances[convId];
      }
    },
    getAllInstances: function() {
      return instances;
    },

    /**
     * Renderiza el componente de conversación en un contenedor
     * Uso: ConversacionInterna.render('mi-contenedor', 'tarea', '123', { height: '400px' })
     */
    render: function(containerId, viewName, entityId, options) {
      var defaults = {
        height: '400px',
        placeholder: 'Escribe un comentario...',
        showFiles: true,
        compact: false,
        autoRefresh: true,
        showHeader: false,
        headerText: 'Conversación'
      };

      var settings = $.extend({}, defaults, options);
      var convId = 'conv_' + viewName + '_' + entityId;
      var $container = $('#' + containerId);

      if($container.length === 0) {
        console.error('[ConversacionInterna.render] Contenedor no encontrado:', containerId);
        return null;
      }

      // Destruir cualquier conversación previa en este contenedor
      var existingConvId = $container.find('.conversacion-interna-container').attr('id');
      if(existingConvId && instances[existingConvId]) {
        console.log('[ConversacionInterna.render] Destruyendo conversación anterior:', existingConvId);
        instances[existingConvId].destroy();
        delete instances[existingConvId];
      }

      // Generar HTML completo
      var html = this.generateHTML(convId, viewName, entityId, settings);

      // Insertar en el contenedor
      $container.html(html);

      // Auto-inicializar
      this.init(convId);

      console.log('[ConversacionInterna.render] Componente renderizado:', convId);

      return convId; // Retorna el ID para poder destruirlo después
    },

    /**
     * Genera el HTML del componente
     */
    generateHTML: function(convId, viewName, entityId, settings) {
      var headerHTML = settings.showHeader ?
        '<div class="conversacion-header"><h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>' + settings.headerText + '</h5></div>' : '';

      var textareaRows = settings.compact ? '1' : '2';

      var dropzoneHTML = settings.showFiles ? `
        <div class="conversacion-dropzone">
          <input type="file" class="conversacion-input-archivos d-none" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
          <div class="dropzone-area text-center p-3 border-start border-end border-bottom">
            <i class="bi bi-cloud-upload" style="font-size: 1.5rem; color: var(--bs-secondary);"></i>
            <p class="mb-0 small">Arrastra archivos aquí o haz clic para seleccionar</p>
          </div>
        </div>
      ` : '';

      var filesPreviewHTML = settings.showFiles ? `
        <div class="conversacion-archivos-preview" style="display: none;">
          <div class="mb-0 border-start border-end" style="border-color: var(--bs-border-color);">
            <div class="p-2" id="files-list"></div>
          </div>
        </div>
      ` : '';

      return `
        <div id="${convId}" class="conversacion-interna-container"
             data-view-name="${viewName}"
             data-entity-id="${entityId}">

          ${headerHTML}

          <div class="conversacion-loading text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="conversacion-postbox">
                <form class="conversacion-form">
                  <div class="input-group">
                    <textarea class="form-control conversacion-input-contenido"
                              rows="${textareaRows}"
                              placeholder="${settings.placeholder}"
                              required
                              style="overflow-y: hidden; resize: none; border-bottom-left-radius: 0;"></textarea>
                    <button type="button" class="btn btn-primary conversacion-btn-enviar" style="border-bottom-right-radius: 0;">
                      Publicar
                    </button>
                  </div>
                  ${filesPreviewHTML}
                  ${dropzoneHTML}
                </form>
              </div>

              <div class="conversacion-comentarios" style="max-height: ${settings.height}; overflow-y: auto;"></div>

              <div class="conversacion-sin-comentarios text-center py-5 text-muted" style="display:none;">
                <i class="bi bi-chat-dots" style="font-size: 3rem; opacity: 0.3;"></i>
                <p>Aún no hay comentarios. Sé el primero en comentar.</p>
              </div>
            </div>
          </div>
        </div>
      `;
    }
  };
})();
