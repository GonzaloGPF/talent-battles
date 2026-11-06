<template>
  <AppButton
    v-if="!isLogged"
    :label="title"
    :modal="modal"
    size="small"
    variant="text"
    class="brand-font"
    @click="openModal"
  >
    <template #modal>
      <AppImage
        src="/images/logo_big.webp"
        width="200"
        class="m-auto"
      />
      <v-tabs
        v-model="tab"
        :items="tabs"
        fixed-tabs
        show-arrows
        centered
        class="lg:min-w-min my-3"
      />
      <AppAlert
        :message="alertText"
        class="mb-3"
        border="start"
        variant="tonal"
      />
      <v-window v-model="tab">
        <v-window-item :value="REGISTER">
          <RegisterForm
            ref="registerFormElement"
            @keyup.enter="submit"
          />
          <o-auth-buttons
            class="mt-5 mt-lg-1"
            @redirected="showModal = false"
          />
        </v-window-item>
        <v-window-item :value="LOGIN">
          <LoginForm
            ref="loginFormElement"
            @keyup.enter="submit"
          />
          <AppButton
            :label="Translator.t('help.dont_have_an_account')"
            size="small"
            color="blue"
            variant="text"
            @click="tab = REGISTER"
          />
        </v-window-item>
        <v-window-item :value="PASSWORD">
          <PasswordForgotForm
            ref="passwordFormElement"
            @keyup.enter="submit"
          />
        </v-window-item>
      </v-window>
    </template>
  </AppButton>
</template>
<script setup>
import EventBus from '@/events/EventBus';
import EventTypes from '@/events/EventTypes';
import {computed, defineAsyncComponent, ref} from 'vue';
import Translator from '@/objects/Translator';
import {useAuth} from '@/stores/auth';
import useHttp from '@/composables/useHttp';
import {storeToRefs} from 'pinia';
import {useDisplay} from 'vuetify';
import AppButton from '@/components/core/AppButton.vue'

const RegisterForm = defineAsyncComponent(() => import('@/components/forms/RegisterForm.vue'))
const LoginForm = defineAsyncComponent(() => import('@/components/forms/LoginForm.vue'))
const PasswordForgotForm = defineAsyncComponent(() => import('@/components/forms/PasswordForgotForm.vue'))
const OAuthButtons = defineAsyncComponent(() => import('@/components/buttons/OAuthButtons.vue'))
const AppAlert = defineAsyncComponent(() => import('@/components/core/AppAlert.vue'))
const AppImage = defineAsyncComponent(() => import('@/components/core/AppImage.vue'))

const REGISTER = 'to_register';
const LOGIN = 'to_login';
const PASSWORD = 'forgot_password';

const { setUser } = useAuth();
const { isLogged } = storeToRefs(useAuth());
const { postJson, loading } = useHttp();

const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value,
    title: title.value,
  },
  listeners: {
    close: () => showModal.value = false,
    ok: submit,
  }
}));
const showModal = ref(false);
const loginFormElement = ref(null);
const registerFormElement = ref(null);
const passwordFormElement = ref(null);
const tab = ref(LOGIN);
const title = computed(() => Translator.actionTitle('enter'));
const { lg, xl, xxl } = useDisplay();
const isBigScreen = computed(() => (lg.value || xl.value || xxl.value));
const tabs = computed(() => [
  {
    title: Translator.tl(REGISTER),
    text: isBigScreen.value ? Translator.tl(REGISTER) : null,
    value: REGISTER,
    prependIcon: 'mdi-account-plus',
  },
  {
    title: Translator.tl(LOGIN),
    text: isBigScreen.value ? Translator.tl(LOGIN) : null,
    value: LOGIN,
    prependIcon: 'mdi-login',
  },
  {
    title: Translator.tl(PASSWORD),
    text: isBigScreen.value ? Translator.tl(PASSWORD) : null,
    value: PASSWORD,
    prependIcon: 'mdi-lock-question',
  },
]);
const alertText = computed(() => {
  const alertTexts = {
    [REGISTER]: Translator.t('help.enter'),
    [LOGIN]: Translator.t('help.login'),
    [PASSWORD]: Translator.t('help.forgot_password'),
  }
  return alertTexts[tab.value];
})
const openModal = () => {
  cleanForms();
  showModal.value = true;
}

const submit = () => {
  switch (tab.value) {
    case REGISTER:
      return registerRequest();
    case LOGIN:
      return loginRequest();
    case PASSWORD:
      return passwordRequest();
  }
}

const loginRequest = () => {
  const data = loginFormElement.value.validate();

  if (! data) return;

  postJson('login', data)
    .then(onSuccess)
}

const registerRequest = () => {
  const data = registerFormElement.value.validate();

  if (! data) return;

  postJson('register', data)
    .then(onSuccess)
}

const passwordRequest = () => {
  const data = passwordFormElement.value.validate();

  if (! data) return;

  postJson('password/forgot', data)
    .then(onSuccess)
}

const onSuccess = ({ data }) => {
  cleanForms();

  showModal.value = false;

  if (tab.value !== PASSWORD) {
    setUser(data);
  }
}

const cleanForms = () => {
  if (loginFormElement.value) {
    // loginFormElement.value.reset();
  }
  if (registerFormElement.value) {
    // registerFormElement.value.reset();
  }
  if (passwordFormElement.value) {
    // passwordFormElement.value.reset();
  }
  EventBus.emit(EventTypes.entered)
}
</script>
