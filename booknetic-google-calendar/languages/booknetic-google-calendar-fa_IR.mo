��          �   %   �      0  u  1  2   �     �  	   �     �  !        $     -     C     K     ]     i     ~     �     �     �  	   �     �     �  -   �  2   *	  /   ]	  =   �	  �  �	  �  �  y   9  #   �     �     �  \   �     \  >   k     �     �     �     �          %  H   D     �     �     �     �  k   �  \   Z  P   �  L                                       
                        	                                                                   1. Live sync;<br/>If you have a few staff, this method would be more convenient for you. When your customers are booking, the plugin will connect to the google calendar and sync busy slots in real-time.<br/>2. Background sync;<br/>For this method, first, you must configure the Cron jobs ( <a href='https://www.booknetic.com/documentation/cron-job' target='_blank'>How to?</a> ). The shorter you set the Cron jobs interval, the more accuracy you will get. This method is usually designed for businesses with a large number of employees and using the "Any Staff" option. Because in this case, when your customer selects Any staff option, it might take more than 30-60 seconds to sync all Staff busy slots with Google calendar. By choosing this method, the plugin Cron Jobs will connect to the Google Calendars in the background at the interval you set up and will store the busy slots of all your employees in your local databases. During booking, it will read the information directly from your database. Errors in this method are inevitable. For example, if you configure your cron jobs to run every 15 minutes, the busy slot you add to your Google calendar will be stored in the plugin's local database every 15 minutes. That is, within these 15 minutes, someone can book an appointment in that time slot. Therefore, the shorter you configure the Cron jobs, the less likely there will be errors. Add customers as attendees in your calendar events Background sync Client ID Client Secret Customers can see other attendees Disabled Don't sync busy slots Enabled Event description Event title Events up to 1 month Events up to 2 month Events up to 3 month Firstly click the login button! Google calendar Live sync Redirect URI SAVE CHANGES Send email invitations to attendees by Google Since what date do events in Google calendar sync? Sync method for busy slots from Google Calendar You do not have sufficient permissions to perform this action Project-Id-Version: test
Report-Msgid-Bugs-To: 
PO-Revision-Date: 2025-03-05 17:54+0400
Last-Translator: admin
Language-Team: فارسی
Language: fa_IR
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=(n != 1);
X-Generator: Poedit 3.5
X-Poedit-Basepath: ../app
X-Poedit-KeywordsList: bkntc__
X-Loco-Version: 2.5.2; wp-5.7.2
X-Poedit-SearchPath-0: .
X-Poedit-SearchPathExcluded-0: Frontend/assets/js/datepicker.min.js
X-Poedit-SearchPathExcluded-1: Frontend/assets/js/intlTelInput.min.js
X-Poedit-SearchPathExcluded-2: Frontend/assets/js/jquery.nicescroll.min.js
X-Poedit-SearchPathExcluded-3: Frontend/assets/js/utilsIntlTelInput.js
 1. Live sync;<br/>اگر چند پرسنل دارید ، این روش برای شما راحت تر است. هنگامی که مشتریان شما در حال رزرو هستند ، این افزونه به تقویم google متصل می شود و اسلات های شلوغ را در زمان واقعی همگام سازی می کند.<br/>
2. Background sync;<br/>2. Background sync;<br/>برای این کار ابتدا باید cron jobs را پیکربندی کنید.( <a href='https://www.booknetic.com/documentation/cron-job' target='_blank'>چگونه؟</a> ).
 هرچه فاصله Cron jobs را کوتاهتر کنید ، دقت بیشتری خواهید داشت.
این روش معمولاً برای مشاغل با تعداد زیادی کارمند و با استفاده از گزینه "Any Staff" طراحی می شود.

 زیرا در این حالت ، هنگامی که مشتری شما هر گزینه کارمندی را انتخاب می کند ، ممکن است بیش از 30-60 ثانیه طول بکشد تا تمام اسلات های شلوغ کارکنان با تقویم Google همگام شود.
با انتخاب این روش ، افزونه Cron Jobs در بازه زمانی که تنظیم کرده اید به تقویم های Google در پس زمینه متصل می شود و اسلات شلوغ همه کارمندان شما را در پایگاه داده محلی شما ذخیره می کند.
 در هنگام رزرو ، اطلاعات را مستقیماً از پایگاه داده شما می خواند.
خطاهای موجود در این روش اجتناب ناپذیر است. به عنوان مثال ، اگر cron jobs خود را طوری تنظیم کنید که هر 15 دقیقه اجرا شود ، اسلات شلوغی که به تقویم Google خود اضافه می کنید هر 15 دقیقه در پایگاه داده پلاگین ذخیره می شود.

 یعنی در این 15 دقیقه کسی می تواند در آن بازه زمانی قرار ملاقات را رزرو کند.

بنابراین ، هرچه کارهای Cron را کوتاهتر پیکربندی کنید ، احتمال بروز خطا کمتر است. مشتریان را به عنوان شرکت کنندگان در رویدادهای تقویم خود اضافه کنید همگام سازی پس زمینه شناسه مشتری رمز مشتری مشتریان می توانند سایر شرکت کنندگان را مشاهده کنند غیرفعال اسلات های شلوغ را همگام سازی نکنید فعالسازی شرح رویداد عنوان رویداد رویدادها تا 1 ماه رویدادها تا 2 ماه رویدادها تا 3 ماه در ابتدا باید بر روی دکمه ورود کلیک کنید تقویم گوگل همگام سازی زنده لینک انتقال ذخیره تغییرات از طرف Google دعوت نامه های ایمیل برای شرکت کنندگان ارسال کنید از چه تاریخی وقایع در تقویم Google همگام سازی می شوند؟ روش همگام سازی برای اسلات شلوغ از تقویم گوگل شما مجوز کافی برای انجام این کار را ندارید 