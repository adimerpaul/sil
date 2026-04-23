<template>
  <q-page class="q-pa-md">
    <q-table :rows="users" :columns="columns" dense wrap-cells flat bordered :rows-per-page-options="[0]"
             title="Usuarios" :filter="filter">
      <template v-slot:top-right>
        <q-btn color="positive" label="Nuevo" @click="userNew" no-caps icon="add_circle_outline" :loading="loading"
               class="q-mr-sm"/>
        <q-btn color="primary" label="Actualizar" @click="usersGet" no-caps icon="refresh" :loading="loading"/>
        <q-input v-model="filter" label="Buscar" dense outlined>
          <template v-slot:append>
            <q-icon name="search"/>
          </template>
        </q-input>
      </template>
      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <q-btn-dropdown label="Opciones" no-caps size="10px" dense color="primary">
            <q-list>
              <q-item clickable @click="userEdit(props.row)" v-close-popup>
                <q-item-section avatar>
                  <q-icon name="edit"/>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Editar</q-item-label>
                </q-item-section>
              </q-item>
              <q-item clickable @click="userDelete(props.row.id)" v-close-popup>
                <q-item-section avatar>
                  <q-icon name="delete"/>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Eliminar</q-item-label>
                </q-item-section>
              </q-item>
              <q-item clickable @click="userEditPassword(props.row)" v-close-popup>
                <q-item-section avatar>
                  <q-icon name="lock_reset"/>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Cambiar contraseña</q-item-label>
                </q-item-section>
              </q-item>
              <q-item clickable @click="cambiarAvatar(props.row)" v-close-popup>
                <q-item-section avatar>
                  <q-icon name="image"/>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Cambiar avatar</q-item-label>
                </q-item-section>
              </q-item>
              <q-item clickable @click="permisosShow(props.row)" v-close-popup>
                <q-item-section avatar>
                  <q-icon name="lock" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Permisos</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>
      <template v-slot:body-cell-role="props">
        <q-td :props="props">
          <q-chip :label="props.row.role"
                  :color="$filters.color(props.row.role)"
                  text-color="white" dense size="14px"/>
        </q-td>
      </template>
      <template v-slot:body-cell-avatar="props">
        <q-td :props="props">
          <q-avatar rounded>
            <q-img :src="`${$url}/../images/${props.row.avatar}`" width="40px" height="40px" v-if="props.row.avatar"/>
            <q-icon name="person" size="40px" v-else/>
          </q-avatar>
        </q-td>
      </template>
      <template v-slot:body-cell-permissions="props">
        <q-td :props="props">
          <div class="row items-center q-col-gutter-xs">

            <!-- hasta 3 chips visibles -->
            <q-chip
              v-for="(perm, idx) in (props.row.permissions || []).slice(0, 3)"
              :key="perm.id"
              dense
              color="grey-3"
              text-color="black"
              size="12px"
              class="q-mr-xs q-mb-xs"
            >
              {{ perm.name }}
            </q-chip>

            <!-- si hay más, badge + tooltip con el listado completo -->
            <template v-if="(props.row.permissions || []).length > 3">
              <q-badge outline color="primary" class="q-ml-xs">
                +{{ (props.row.permissions || []).length - 3 }}
                <q-tooltip anchor="top middle" self="bottom middle" :offset="[0,8]">
                  <div class="text-left">
                    <div
                      v-for="perm in props.row.permissions"
                      :key="perm.id"
                    >• {{ perm.name }}</div>
                  </div>
                </q-tooltip>
              </q-badge>
            </template>

            <!-- sin permisos -->
            <q-badge v-if="!(props.row.permissions || []).length" color="grey-5" text-color="white" outline>
              Sin permisos
            </q-badge>
          </div>
        </q-td>
      </template>
      <!--      <template v-slot:body-cell-permisos="props">-->
      <!--        <q-td :props="props">-->
      <!--          <ul class="pm-0">-->
      <!--            <li class="pm-0" v-for="permiso in props.row.userPermisos" :key="permiso.id">-->
      <!--              {{ permiso?.permiso?.nombre }}-->
      <!--            </li>-->
      <!--          </ul>-->
      <!--        </q-td>-->
      <!--      </template>-->
    </q-table>
<!--    <pre>{{ users }}</pre>-->
    <q-dialog v-model="userDialog" persistent>
      <q-card style="width: 400px">
        <q-card-section class="q-pb-none row items-center">
          <div>
            {{ actionUser }} user
          </div>
          <q-space/>
          <q-btn icon="close" flat round dense @click="userDialog = false"/>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <q-form @submit="user.id ? userPut() : userPost()">
            <q-input v-model="user.name" label="Nombre" dense outlined :rules="[val => !!val || 'Campo requerido']"/>
            <q-input v-model="user.username" label="Usuario" dense outlined
                     :rules="[val => !!val || 'Campo requerido']"/>
            <q-input v-model="user.email" label="Email" dense outlined hint="" />
            <q-input v-model="user.celular" label="Celular" dense outlined />
            <q-input v-model="user.password" label="Contraseña" dense outlined
                     :rules="[val => !!val || 'Campo requerido']" v-if="!user.id"/>
            <q-select v-model="user.role" label="Rol" dense outlined :options="roles"
                      :rules="[val => !!val || 'Campo requerido']"/>
<!--            area-->
            <q-select v-model="user.area_id" label="Area" dense outlined :options="areas"
                      :option-label="'name'"
                      :option-value="'id'"
                      emit-value map-options
                      :rules="[val => !!val || 'Campo requerido']"/>
<!--            <pre>{{areas}}</pre>-->
<!--            <pre>{{user.area_id}}</pre>-->
            <q-select v-model="user.establecimiento_id" label="Establecimiento" dense outlined
                      :options="establecimientos.map(e => ({ label: e.nombre, value: e.id }))"
                      emit-value map-options
                      :rules="[val => !!val || 'Campo requerido']"/>
            <div class="text-right">
              <q-btn color="negative" label="Cancelar" @click="userDialog = false" no-caps :loading="loading"/>
              <q-btn color="primary" label="Guardar" type="submit" no-caps :loading="loading" class="q-ml-sm"/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <!--    dialogPermisos-->
    <!--    <q-dialog v-model="dialogPermisos" persistent>-->
    <!--      <q-card>-->
    <!--        <q-card-section class="q-pb-none row items-center text-bold">-->
    <!--          Permisos-->
    <!--          <q-space />-->
    <!--          <q-btn icon="close" flat round dense @click="dialogPermisos = false" />-->
    <!--        </q-card-section>-->
    <!--        <q-card-section class="q-pt-none">-->
    <!--          <q-list dense>-->
    <!--            <q-item v-for="permiso in permissions" :key="permiso.id">-->
    <!--              <q-item-section>-->
    <!--                <q-item-label>{{ permiso.nombre }}</q-item-label>-->
    <!--              </q-item-section>-->
    <!--              <q-item-section side>-->
    <!--                <q-toggle v-model="permiso.checked" />-->
    <!--              </q-item-section>-->
    <!--            </q-item>-->
    <!--          </q-list>-->
    <!--          &lt;!&ndash;          <pre>{{ user }}</pre>&ndash;&gt;-->
    <!--        </q-card-section>-->
    <!--        <q-card-actions align="right">-->
    <!--          <q-btn color="negative" label="Cancelar" @click="dialogPermisos = false" no-caps :loading="loading" />-->
    <!--          <q-btn color="primary" label="Guardar" @click="permisosPost" no-caps :loading="loading" />-->
    <!--        </q-card-actions>-->
    <!--      </q-card>-->
    <!--    </q-dialog>-->
    <q-dialog v-model="cambioAvatarDialogo" persistent>
      <q-card>
        <q-card-section class="q-pb-none row items-center text-bold">
          Cambiar avatar
          <q-space/>
          <q-btn icon="close" flat round dense @click="cambioAvatarDialogo = false"/>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <q-form @submit="userPut()">
            <!--            <q-avatar>-->
            <div>
              <div style="position: relative;top: 0;left: 0;">
                <q-btn icon="edit" size="10px" class="absolute q-mt-sm q-ml-sm" @click="$refs.fileInput.click()" dense
                       outline label="Cambiar foto" no-caps/>
              </div>
              <img :src="`${$url}/../images/${user.avatar}`" width="300px" height="300px" v-if="user.avatar"/>
              <q-icon name="person" size="100px" v-else/>
              <input ref="fileInput" type="file" style="display: none;" @change="onFileChange" accept="image/*"/>
            </div>
            <!--            </q-avatar>-->
            <div class="text-right">
              <q-btn color="negative" label="Cancelar" @click="cambioAvatarDialogo = false" no-caps :loading="loading"/>
              <q-btn color="primary" label="Guardar" type="submit" no-caps :loading="loading" class="q-ml-sm"/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="dialogPermisos" persistent :maximized="$q.screen.lt.sm">
      <q-card class="permission-card">
        <q-card-section class="permission-card__header">
          <div class="row items-center no-wrap full-width">
            <q-icon name="admin_panel_settings" size="22px" class="text-primary q-mr-sm" />
            <div class="permission-card__title">
              <div class="text-weight-bold">Permisos</div>
              <div class="text-caption text-grey-7 ellipsis">
                {{ user.username }}
              </div>
            </div>
            <q-space />
            <q-badge color="primary" outline class="q-mr-sm">
              {{ selectedPermissionsCount }} activos
            </q-badge>
            <q-btn icon="close" flat round dense @click="dialogPermisos = false" />
          </div>
        </q-card-section>

        <q-card-section class="permission-card__filters">
          <q-input v-model="permFilter" dense outlined clearable placeholder="Filtrar permisos...">
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </q-card-section>

        <q-separator />

        <q-card-section class="permission-card__body">
          <q-list dense class="permission-groups">
            <q-expansion-item
              v-for="group in filteredPermissionGroups"
              :key="group.key"
              dense
              dense-toggle
              expand-separator
              :default-opened="group.defaultOpened || Boolean(permFilter)"
              header-class="permission-group-header"
            >
              <template v-slot:header>
                <q-item-section avatar class="permission-group-header__icon">
                  <q-icon :name="group.icon" size="19px" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold">
                    {{ group.label }}
                  </q-item-label>
                  <q-item-label caption>
                    {{ group.checkedCount }}/{{ group.permissions.length }} activos
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <div class="row no-wrap">
                    <q-btn dense flat round icon="done_all" size="sm" @click.stop="setGroupPermissions(group, true)">
                      <q-tooltip>Activar grupo</q-tooltip>
                    </q-btn>
                    <q-btn dense flat round icon="remove_done" size="sm" @click.stop="setGroupPermissions(group, false)">
                      <q-tooltip>Desactivar grupo</q-tooltip>
                    </q-btn>
                  </div>
                </q-item-section>
              </template>

              <div class="permission-grid">
                <q-item
                  v-for="perm in group.permissions"
                  :key="perm.id"
                  dense
                  clickable
                  class="permission-row"
                  @click="perm.checked = !perm.checked"
                >
                  <q-item-section avatar class="permission-row__icon">
                    <q-icon
                      :name="perm.checked ? 'check_circle' : 'radio_button_unchecked'"
                      :color="perm.checked ? 'primary' : 'grey-5'"
                      size="18px"
                    />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label lines="2" class="permission-row__label">
                      {{ perm.name }}
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-toggle v-model="perm.checked" dense color="primary" @click.stop />
                  </q-item-section>
                </q-item>
              </div>
            </q-expansion-item>
          </q-list>

          <div v-if="filteredPermissionGroups.length === 0" class="permission-empty">
            No hay permisos para mostrar
          </div>
        </q-card-section>

        <q-card-actions align="right" class="permission-card__actions">
          <q-btn color="negative" label="Cancelar" @click="dialogPermisos = false" no-caps :loading="loading" />
          <q-btn color="primary" label="Guardar" @click="permisosPost" no-caps :loading="loading" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>
<script>
import moment from 'moment'

const HOSPITAL_PERMISSION_NAMES = [
  'Usuarios',
  'Pacientes',
  'Doctores',
  'Establecimientos',
  'Servicios',
]

const ALMACEN_PERMISSION_NAMES = [
  'Módulo inventario',
  'Modulo compras',
  'Modulo detalle compras',
  'Módulo movimiento',
  'Módulo de faltantes y sobrantes',
  'Ver Pedidos',
  'Crear Pedidos',
  'Editar Pedidos',
  'Anular Pedidos',
  'Imprimir Pedidos',
]

const PERMISSION_GROUPS = [
  {
    key: 'hospital',
    label: 'Hospital',
    icon: 'local_hospital',
    defaultOpened: true,
    names: HOSPITAL_PERMISSION_NAMES,
  },
  {
    key: 'laboratorio',
    label: 'Laboratorio',
    icon: 'science',
    defaultOpened: true,
    names: [],
  },
  {
    key: 'almacen',
    label: 'Almacén',
    icon: 'warehouse',
    defaultOpened: true,
    names: ALMACEN_PERMISSION_NAMES,
  },
]

export default {
  name: 'UsuariosPage',
  data() {
    return {
      users: [],
      user: {},
      userDialog: false,
      loading: false,
      actionUser: '',
      gestiones: [],
      filter: '',
      roles: ['Medico', 'Admision', 'Preanalitica', 'Analitica', 'Administrador'],
      // areas : [
      //   'Administracion',
      //   'Hematologia',
      //   'Quimica Sanguinea',
      //   'Uruanalisis y Parasitologia',
      //   'Bacterologia',
      //   'Inmunologia',
      // ],
      columns: [
        {name: 'actions', label: 'Acciones', align: 'center'},
        {name: 'name', label: 'Nombre', align: 'left', field: 'name'},
        {name: 'username', label: 'Usuario', align: 'left', field: 'username'},
        {name: 'avatar', label: 'Avatar', align: 'left', field: (row) => row.avatar},
        {name: 'role', label: 'Rol', align: 'left', field: 'role'},
        {name: 'celular', label: 'Celular', align: 'left', field: 'celular'},
        { name: 'permissions', label: 'Permisos', align: 'left',
          field: row => (row.permissions || []).map(p => p.name).join(', ')
        },
        // area
        {name: 'area', label: 'Area', align: 'left', field: row => row.area?.name || ''},
        // establecimiento
        {name: 'establecimiento', label: 'Establecimiento', align: 'left', field: row => row.establecimiento?.nombre || ''},
      ],
      permissions: [],
      dialogPermisos: false,
      permFilter: '',
      cambioAvatarDialogo: false,
      docentes: [],
      establecimientos: [],
      areas: [],
    }
  },
  async mounted() {
    // this.docentes = await this.$axios.get('docentes').then(res => res.data)
    this.usersGet()
    this.areasGet()
    // this.permissionsGet()
    this.$axios.get('establecimientos').then(res => {
      this.establecimientos = res.data
    }).catch(error => {
      this.$alert.error(error.response.data.message)
    })
  },
  methods: {
    areasGet() {
      this.$axios.get('areas').then(res => {
        this.areas = res.data
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      })
    },
    onFileChange(event) {
      const file = event.target.files[0]
      const formData = new FormData()
      formData.append('avatar', file)
      this.loading = true
      this.$axios.post(this.user.id + '/avatar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(res => {
        this.cambioAvatarDialogo = false
        this.$alert.success('Avatar actualizado')
        this.usersGet()
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    cambiarAvatar(user) {
      this.cambioAvatarDialogo = true
      this.user = {...user}
    },
    // permisosPost() {
    //   this.loading = true
    //   const permissions = this.permissions.filter(p => p.checked).map(p => p.id)
    //   this.$axios.post('permisos', {
    //     user_id: this.user.id,
    //     permissions
    //   }).then(res => {
    //     this.dialogPermisos = false
    //     this.$alert.success('Permisos actualizados')
    //     this.usersGet()
    //   }).catch(error => {
    //     this.$alert.error(error.response.data.message)
    //   }).finally(() => {
    //     this.loading = false
    //   })
    // },
    // permissionsGet() {
    //   this.$axios.get('permisos').then(res => {
    //     this.permissions = res.data
    //   }).catch(error => {
    //     this.$alert.error(error.response.data.message)
    //   })
    // },
    userNew() {
      this.user = {
        name: '',
        email: '',
        celular: '',
        password: '',
        area_id: 1,
        username: '',
        cargo: '',
        role: 'Usuario',
      }
      this.actionUser = 'Nuevo'
      this.userDialog = true
    },
    usersGet() {
      this.loading = true
      this.users = []
      this.$axios.get('users').then(res => {
        this.users = res.data
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    gestionGet() {
      this.loading = true
      this.$axios.get('gestiones').then(res => {
        this.gestiones = res.data
        this.loading = false
      }).catch(error => {
        this.$alert.error(error.response.data.message)
        this.loading = false
      })
    },
    userPost() {
      this.loading = true
      this.$axios.post('users', this.user).then(res => {
        this.userDialog = false
        this.$alert.success('User creado')
        this.usersGet()
        // this.users.push(res.data)
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    userPut() {
      this.loading = true
      this.$axios.put('users/' + this.user.id, this.user).then(res => {
        this.usersGet()
        this.userDialog = false
        this.$alert.success('User actualizado')
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    async permisosShow(user) {
      this.user = { ...user }
      this.dialogPermisos = true
      this.loading = true
      try {
        // 1) Traer todos los permisos
        const all = await this.$axios.get('permissions').then(r => r.data)
        // 2) Traer permisos del usuario (array de IDs)
        const userPermIds = await this.$axios.get(`users/${user.id}/permissions`).then(r => r.data)

        // 3) Mezclar con checked
        this.permissions = all.map(p => ({
          ...p,
          checked: userPermIds.includes(p.id)
        }))
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando permisos')
      } finally {
        this.loading = false
      }
    },

    async permisosPost() {
      this.loading = true
      try {
        const ids = this.permissions.filter(p => p.checked).map(p => p.id)
        await this.$axios.put(`users/${this.user.id}/permissions`, { permissions: ids })
        this.dialogPermisos = false
        this.$alert.success('Permisos actualizados')
        this.usersGet()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.loading = false
      }
    },
    permissionGroupFor(permissionName) {
      if (HOSPITAL_PERMISSION_NAMES.includes(permissionName)) return PERMISSION_GROUPS[0]
      if (ALMACEN_PERMISSION_NAMES.includes(permissionName)) return PERMISSION_GROUPS[2]
      return PERMISSION_GROUPS[1]
    },
    normalizePermissionText(value) {
      return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
    },
    sortPermissionsByGroupOrder(permissions, orderNames) {
      const order = new Map(orderNames.map((name, index) => [name, index]))
      return [...permissions].sort((a, b) => {
        const aIndex = order.has(a.name) ? order.get(a.name) : 999
        const bIndex = order.has(b.name) ? order.get(b.name) : 999
        if (aIndex !== bIndex) return aIndex - bIndex
        return a.name.localeCompare(b.name)
      })
    },
    setGroupPermissions(group, checked) {
      group.permissions.forEach(permission => {
        permission.checked = checked
      })
    },
    userEditPassword(user) {
      this.user = {...user}
      this.$alert.dialogPrompt('Nueva contraseña', 'Ingrese la nueva contraseña', 'password')
        .onOk(password => {
          this.$axios.put('updatePassword/' + user.id, {
            password: password
          }).then(res => {
            this.usersGet()
            this.$alert.success('Contraseña actualizada de ' + user.username)
          }).catch(error => {
            this.$alert.error(error.response.data.message)
          })
        })
    },
    userEdit(user) {
      this.user = {...user}
      this.actionUser = 'Editar'
      this.userDialog = true
    },
    userDelete(id) {
      this.$alert.dialog('¿Desea eliminar el user?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete('users/' + id).then(res => {
            this.usersGet()
            this.$alert.success('User eliminado')
          }).catch(error => {
            this.$alert.error(error.response.data.message)
          }).finally(() => {
            this.loading = false
          })
        })
    },
  },
  computed: {
    permissionGroups() {
      const groups = PERMISSION_GROUPS.map(group => ({
        ...group,
        permissions: [],
      }))
      const groupMap = new Map(groups.map(group => [group.key, group]))

      this.permissions.forEach(permission => {
        const group = this.permissionGroupFor(permission.name)
        groupMap.get(group.key).permissions.push(permission)
      })

      return groups
        .map(group => ({
          ...group,
          permissions: this.sortPermissionsByGroupOrder(group.permissions, group.names),
        }))
        .filter(group => group.permissions.length > 0)
    },
    filteredPermissionGroups() {
      const filter = this.normalizePermissionText(this.permFilter)

      return this.permissionGroups
        .map(group => {
          const permissions = filter
            ? group.permissions.filter(permission => this.normalizePermissionText(permission.name).includes(filter))
            : group.permissions

          return {
            ...group,
            permissions,
            checkedCount: permissions.filter(permission => permission.checked).length,
          }
        })
        .filter(group => group.permissions.length > 0)
    },
    selectedPermissionsCount() {
      return this.permissions.filter(permission => permission.checked).length
    }
  },
}
</script>

<style scoped>
.permission-card {
  width: min(94vw, 780px);
  max-width: 780px;
}

.permission-card__header {
  padding: 12px 14px 8px;
}

.permission-card__title {
  min-width: 0;
  line-height: 1.15;
}

.permission-card__filters {
  padding: 6px 14px 12px;
}

.permission-card__body {
  max-height: min(62vh, 560px);
  overflow: auto;
  padding: 8px 10px;
  background: #f7fafc;
}

.permission-groups {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

:deep(.permission-group-header) {
  min-height: 40px;
  border: 1px solid #dfe7ee;
  border-radius: 8px;
  background: #ffffff;
}

.permission-group-header__icon {
  min-width: 30px;
}

.permission-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 6px;
  padding: 8px 2px 2px;
}

.permission-row {
  min-height: 36px;
  padding: 3px 8px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
}

.permission-row__icon {
  min-width: 28px;
}

.permission-row__label {
  font-size: 12px;
  line-height: 1.15;
  letter-spacing: 0;
}

.permission-empty {
  padding: 24px 8px;
  color: #607d8b;
  text-align: center;
}

.permission-card__actions {
  padding: 10px 14px;
  background: #ffffff;
}

@media (max-width: 599px) {
  .permission-card {
    width: 100%;
    height: 100%;
    max-width: none;
    border-radius: 0;
  }

  .permission-card__body {
    max-height: calc(100vh - 176px);
    padding: 8px;
  }

  .permission-grid {
    grid-template-columns: 1fr;
  }

  .permission-card__actions {
    position: sticky;
    bottom: 0;
    z-index: 1;
  }
}
</style>
